<?php
namespace App\Services;
use App\Models\DailyBusinessSummary;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CustomerPayment;
use App\Models\SupplierPayment;
use Carbon\Carbon;


class DailyBusinessSummaryService


{
    public function generateDailySummary(int $shopId, string $businessDate){
      $date= Carbon::parse($businessDate)->toDateString();
      $saleQuery= Sale::where('shop_id',$shopId)->whereDate('sale_date',$date);
      $purchaseQuery= Purchase::where('shop_id',$shopId)->whereDate('purchase_date',$date);
      $customerPaymentQuery=CustomerPayment::where('shop_id',$shopId)->whereDate('payment_date',$date);
      $supplierPaymentQuery=SupplierPayment::where('shop_id',$shopId)->whereDate('payment_date',$date);
      return DailyBusinessSummary::updateOrCreate(
        ['shop_id'=>$shopId,'business_date'=>$date],
        [
            'total_sales'=>(float)(clone $saleQuery)->sum('total_amount'),
            'total_purchases'=>(float)(clone $purchaseQuery)->sum('total_amount'),
            'customer_payments'=>(float)(clone $customerPaymentQuery)->sum('amount'),
            'supplier_payments'=>(float)(clone $supplierPaymentQuery)->sum('amount'),
            'sales_count'=>(int)(clone $saleQuery)->count(),
            'purchases_count'=>(int)(clone $purchaseQuery)->count(),
        ]
      );
    }
    public function getMonthlyReport(int $shopId, string $month){
        $startDate=Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endDate=Carbon::parse($month . '-01')->endOfMonth()->toDateString();
        $query=DailyBusinessSummary::where('shop_id',$shopId)->whereBetween('business_date',[$startDate,$endDate]);
        return [
            'shop_id'=>$shopId,
            'month'=>Carbon::parse($month . '-01')->format('Y-m'),
            'total_sales'=>(float)(clone $query)->sum('total_sales'),
            'total_purchases'=>(float)(clone $query)->sum('total_purchases'),
            'customer_payments'=>(float)(clone $query)->sum('customer_payments'),
            'supplier_payments'=>(float)(clone $query)->sum('supplier_payments'),
            'sales_count'=>(int)(clone $query)->sum('sales_count'),
            'purchases_count'=>(int)(clone $query)->sum('purchases_count'),
        ];
    }

    public function getDailySummary(int $shopId, string $businessDate){
        $date= Carbon::parse($businessDate)->toDateString();
        return DailyBusinessSummary::where('shop_id',$shopId)->where('business_date',$date)->first();
    }

}

