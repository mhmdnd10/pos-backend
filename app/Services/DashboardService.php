<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;

class DashboardService
{
    public function summary(): array
    {
        $today = today()->toDateString();

        return [
            'today_sales' => Sale::whereDate('sale_date', $today)->where('invoice_status', 'finalized')->sum('total_amount'),
            'today_purchases' => Purchase::whereDate('created_at', today())->sum('total_amount'),
            'today_returns' => Sale::whereDate('sale_date', $today)->where('invoice_status', 'cancelled')->sum('total_amount'),
            'total_customers' => Customer::count(),
            'total_suppliers' => Supplier::count(),
            'lowest_stock_products' => Product::whereColumn('stock_quantity', '<', 'minimum_stock_alert')->count(),
        ];
    }
}