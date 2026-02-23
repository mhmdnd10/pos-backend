<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;


class DashboardService extends Controller
{
    public function summary()
    {
        $today_sales=Sale::whereDate('created_at',today())->sum('total_amount');
        $today_purchases=Purchase::whereDate('created_at',today())->sum('total_amount');
        $total_customers=Customer::count();
        $total_suppliers=Supplier::count();
        $lowest_stock_products=Product::whereColumn('stock_quantity','<','minimum_stock_alert')->count();
        return [
            'today_sales'=>$today_sales,
            'today_purchases'=>$today_purchases,
            'total_customers'=>$total_customers,
            'total_suppliers'=>$total_suppliers,
            'lowest_stock_products'=>$lowest_stock_products

        ];
      }
}
