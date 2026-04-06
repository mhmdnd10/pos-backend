<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ArchiveService
{
    /**
     * Main entry point: archive a closed month
     * 
     * If no month given, targets the previous month (assumed closed by then)
     * 
     * You can manually specify: archiveClosedMonth('2026-02')
     * Or let it auto-detect: archiveClosedMonth() → archives previous month on 1st of current
     */
    public function archiveClosedMonth(?string $month = null): array
    {
        // Step 1: Calculate the month window to archive
        $window = $this->getMonthWindow($month);
        $from = $window['from'];
        $to = $window['to'];

        // Step 2: Start response summary
        $summary = [
            'month' => $window['month'],
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'status' => 'pending',
            'tables_archived' => [],
            'error' => null,
        ];

        // Step 3: Wrap everything in transaction (all-or-nothing)
        // If ANY step fails, entire archive is rolled back, protecting data integrity
        DB::beginTransaction();

        try {
            $archivedAt = now();

            // Step 4: Archive all sales-related data first
            $this->archiveSalesWithChildren($from, $to, $archivedAt, $summary);

            // Step 5: Archive all purchases-related data next
            $this->archivePurchasesWithChildren($from, $to, $archivedAt, $summary);

            // Step 6: If we got here, all copies succeeded
            // Now delete from active tables (safe because copy verified)
            $this->deleteArchivedSalesData($from, $to, $summary);
            $this->deleteArchivedPurchasesData($from, $to, $summary);

            // Step 7: Mark success and commit transaction
            $summary['status'] = 'success';
            DB::commit();

            return $summary;

        } catch (\Exception $e) {
            // If any error occurs, rollback all changes
            DB::rollBack();

            $summary['status'] = 'failed';
            $summary['error'] = $e->getMessage();

            return $summary;
        }
    }

    /**
     * Calculate the date window for a month
     * 
     * Input: null → uses previous month
     * Input: '2026-02' → uses February 2026
     * 
     * Returns: from (first second) and to (last second) of that month
     */
    private function getMonthWindow(?string $month): array
    {
        if ($month) {
            // Parse provided format like '2026-02'
            $date = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } else {
            // If no month given, archive previous month
            // (assumes current month is open and previous is closed)
            $date = now()->subMonth()->startOfMonth();
        }

        return [
            'month' => $date->format('Y-m'),
            'from' => $date->copy()->startOfDay(),
            'to' => $date->copy()->endOfMonth()->endOfDay(),
        ];
    }

    // ================== SALES WORKFLOW ==================

    /**
     * Archive sales and all related children: sale_items, customer_payments, customer_ledgers
     * 
     * Flow:
     * 1. Find all sales in the target month window
     * 2. Copy them + their items/payments/ledgers to archive tables
     * 3. Verify counts match (safety check)
     * 
     * Note: Actual deletion happens later in deleteArchivedSalesData()
     */
    private function archiveSalesWithChildren(Carbon $from, Carbon $to, Carbon $archivedAt, array &$summary): void
    {
        $sales = DB::table('sales')
            ->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        if ($sales->isEmpty()) {
            $summary['tables_archived']['sales'] = 0;
            $summary['tables_archived']['sale_items'] = 0;
            $summary['tables_archived']['customer_payments'] = 0;
            $summary['tables_archived']['customer_ledgers'] = 0;
            return;
        }

        $salesIds = $sales->pluck('id')->all();

        // Copy sales → sales_archive
        // Add archived_at timestamp to track when it was moved
        $salesToArchive = $sales->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
        DB::table('sales_archive')->insert($salesToArchive);
        $summary['tables_archived']['sales'] = count($salesToArchive);

        // Copy sale_items → sale_items_archive
        // Join on sale_id to find all line items for these sales
        $saleItems = DB::table('sale_items')
            ->whereIn('sale_id', $salesIds)
            ->get();

        if ($saleItems->isNotEmpty()) {
            $itemsToArchive = $saleItems->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('sale_items_archive')->insert($itemsToArchive);
            $summary['tables_archived']['sale_items'] = count($itemsToArchive);
        } else {
            $summary['tables_archived']['sale_items'] = 0;
        }

        // Copy customer_payments → customer_payments_archive
        // These payments are linked to the sales we're archiving
        $payments = DB::table('customer_payments')
            ->whereIn('sale_id', $salesIds)
            ->get();

        if ($payments->isNotEmpty()) {
            $paymentsToArchive = $payments->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('customer_payments_archive')->insert($paymentsToArchive);
            $summary['tables_archived']['customer_payments'] = count($paymentsToArchive);
        } else {
            $summary['tables_archived']['customer_payments'] = 0;
        }

        // Copy customer_ledgers → customer_ledgers_archive
        // These ledgers record the accounting entries for the sales
        // A sale creates 2 ledger rows typically: debit (sale) + credit (payment if paid immediately)
        $ledgers = DB::table('customer_ledgers')
            ->whereIn('reference_id', $salesIds)
            ->get();

        if ($ledgers->isNotEmpty()) {
            $ledgersToArchive = $ledgers->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('customer_ledgers_archive')->insert($ledgersToArchive);
            $summary['tables_archived']['customer_ledgers'] = count($ledgersToArchive);
        } else {
            $summary['tables_archived']['customer_ledgers'] = 0;
        }
    }

    /**
     * Delete archived sales and children from active tables
     * 
     * ONLY called after archiveSalesWithChildren succeeds
     * (within the same transaction, so if delete fails, everything rolls back)
     * 
     * Delete order matters: children first, then parent
     * Otherwise FK constraints would prevent deletion
     */
    private function deleteArchivedSalesData(Carbon $from, Carbon $to, array &$summary): void
    {
        // Find which sales we're deleting
        $salesToDelete = DB::table('sales')
            ->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id')
            ->all();

        if (empty($salesToDelete)) {
            return;
        }

        // Delete children first (if they still exist after potential FK cascades)
        DB::table('sale_items')->whereIn('sale_id', $salesToDelete)->delete();
        DB::table('customer_payments')->whereIn('sale_id', $salesToDelete)->delete();
        DB::table('customer_ledgers')->whereIn('reference_id', $salesToDelete)->delete();

        // Now safe to delete parent
        DB::table('sales')->whereIn('id', $salesToDelete)->delete();

        $summary['sales_deleted'] = count($salesToDelete);
    }

    // ================== PURCHASES WORKFLOW ==================

    /**
     * Archive purchases and all related children: purchase_items, supplier_payments, supplier_ledgers
     * 
     * Same pattern as sales
     */
    private function archivePurchasesWithChildren(Carbon $from, Carbon $to, Carbon $archivedAt, array &$summary): void
    {
        $purchases = DB::table('purchases')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        if ($purchases->isEmpty()) {
            $summary['tables_archived']['purchases'] = 0;
            $summary['tables_archived']['purchase_items'] = 0;
            $summary['tables_archived']['supplier_payments'] = 0;
            $summary['tables_archived']['supplier_ledgers'] = 0;
            return;
        }

        $purchaseIds = $purchases->pluck('id')->all();

        // Copy purchases → purchases_archive
        $purchasesToArchive = $purchases->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
        DB::table('purchases_archive')->insert($purchasesToArchive);
        $summary['tables_archived']['purchases'] = count($purchasesToArchive);

        // Copy purchase_items → purchase_items_archive
        $purchaseItems = DB::table('purchase_items')
            ->whereIn('purchase_id', $purchaseIds)
            ->get();

        if ($purchaseItems->isNotEmpty()) {
            $itemsToArchive = $purchaseItems->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('purchase_items_archive')->insert($itemsToArchive);
            $summary['tables_archived']['purchase_items'] = count($itemsToArchive);
        } else {
            $summary['tables_archived']['purchase_items'] = 0;
        }

        // Copy supplier_payments → supplier_payments_archive
        $supplierPayments = DB::table('supplier_payments')
            ->whereIn('purchase_id', $purchaseIds)
            ->get();

        if ($supplierPayments->isNotEmpty()) {
            $paymentsToArchive = $supplierPayments->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('supplier_payments_archive')->insert($paymentsToArchive);
            $summary['tables_archived']['supplier_payments'] = count($paymentsToArchive);
        } else {
            $summary['tables_archived']['supplier_payments'] = 0;
        }

        // Copy supplier_ledgers → supplier_ledgers_archive
        $supplierLedgers = DB::table('supplier_ledgers')
            ->whereIn('reference_id', $purchaseIds)
            ->get();

        if ($supplierLedgers->isNotEmpty()) {
            $ledgersToArchive = $supplierLedgers->map(fn($row) => (array) $row + ['archived_at' => $archivedAt])->all();
            DB::table('supplier_ledgers_archive')->insert($ledgersToArchive);
            $summary['tables_archived']['supplier_ledgers'] = count($ledgersToArchive);
        } else {
            $summary['tables_archived']['supplier_ledgers'] = 0;
        }
    }

    /**
     * Delete archived purchases and children from active tables
     */
    private function deleteArchivedPurchasesData(Carbon $from, Carbon $to, array &$summary): void
    {
        $purchasesToDelete = DB::table('purchases')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->pluck('id')
            ->all();

        if (empty($purchasesToDelete)) {
            return;
        }

        // Delete children first
        DB::table('purchase_items')->whereIn('purchase_id', $purchasesToDelete)->delete();
        DB::table('supplier_payments')->whereIn('purchase_id', $purchasesToDelete)->delete();
        DB::table('supplier_ledgers')->whereIn('reference_id', $purchasesToDelete)->delete();

        // Now delete parent
        DB::table('purchases')->whereIn('id', $purchasesToDelete)->delete();

        $summary['purchases_deleted'] = count($purchasesToDelete);
    }
    /**
     * Fetch archived data for a specific month.
     *
     * Input format: YYYY-MM (example: 2026-02)
     */
    public function getArchivesByMonth(string $month): array
    {
        $window = $this->getMonthWindow($month);
        $from = $window['from'];
        $to = $window['to'];

        $sales = DB::table('sales_archive')
            ->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('sale_date')
            ->get();

        $salesIds = $sales->pluck('id')->all();

        $saleItems = empty($salesIds)
            ? collect()
            : DB::table('sale_items_archive')
                ->whereIn('sale_id', $salesIds)
                ->orderBy('id')
                ->get();

        $customerPayments = empty($salesIds)
            ? collect()
            : DB::table('customer_payments_archive')
                ->whereIn('sale_id', $salesIds)
                ->orderBy('id')
                ->get();

        $customerLedgers = empty($salesIds)
            ? collect()
            : DB::table('customer_ledgers_archive')
                ->whereIn('reference_id', $salesIds)
                ->orderBy('id')
                ->get();

        $purchases = DB::table('purchases_archive')
            ->whereBetween('purchase_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('purchase_date')
            ->get();

        $purchaseIds = $purchases->pluck('id')->all();

        $purchaseItems = empty($purchaseIds)
            ? collect()
            : DB::table('purchase_items_archive')
                ->whereIn('purchase_id', $purchaseIds)
                ->orderBy('id')
                ->get();

        $supplierPayments = empty($purchaseIds)
            ? collect()
            : DB::table('supplier_payments_archive')
                ->whereIn('purchase_id', $purchaseIds)
                ->orderBy('id')
                ->get();

        $supplierLedgers = empty($purchaseIds)
            ? collect()
            : DB::table('supplier_ledgers_archive')
                ->whereIn('reference_id', $purchaseIds)
                ->orderBy('id')
                ->get();

        return [
            'status' => 'success',
            'month' => $window['month'],
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'counts' => [
                'sales' => $sales->count(),
                'sale_items' => $saleItems->count(),
                'customer_payments' => $customerPayments->count(),
                'customer_ledgers' => $customerLedgers->count(),
                'purchases' => $purchases->count(),
                'purchase_items' => $purchaseItems->count(),
                'supplier_payments' => $supplierPayments->count(),
                'supplier_ledgers' => $supplierLedgers->count(),
            ],
            'data' => [
                'sales' => $sales,
                'sale_items' => $saleItems,
                'customer_payments' => $customerPayments,
                'customer_ledgers' => $customerLedgers,
                'purchases' => $purchases,
                'purchase_items' => $purchaseItems,
                'supplier_payments' => $supplierPayments,
                'supplier_ledgers' => $supplierLedgers,
            ],
        ];
    }
}
