<?php
namespace App\Services;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\DB;



class SaleService
{
    public function create(array $data)
    {
        return DB::transaction(function() use ($data){
            $sale=Sale::create([
                'customer_id'=>$data['customer_id'],
                'total_amount'=>$data['total_amount'],
                'paid_amount'=>$data['paid_amount'],
                'remaining_amount'=>$data['total_amount'] - $data['paid_amount'],
                'payment_status'=>$data['paid_amount'] >= $data['total_amount'] ? 'paid' : ($data['paid_amount'] > 0 ? 'partial' : 'unpaid'),
            ]);
            foreach($data['items'] as $item){
               $itemModel= $sale->saleItems()->create([
                    'product_id'=>$item['product_id'],
                    'quantity'=>$item['quantity'],
                    'unit_price'=>$item['unit_price'],
                    'total_price'=>$item['quantity'] * $item['unit_price'],
                ]);
                $itemModel->product()->decrement('stock_quantity',$item['quantity']);
            }
            CustomerLedger::create([
                'customer_id'=>$data['customer_id'],
                'type'=>'sale',
                'reference_id'=>$sale->id,
                'reference_number'=>'SAL-' .$sale->id,
                'debit'=>$data['total_amount'],
                'credit'=>0,
            ]);
            if($data['paid_amount'] > 0){
                CustomerLedger::create([
                    'customer_id'=>$data['customer_id'],
                    'type'=>'payment',
                    'reference_id'=>$sale->id,
                    'reference_number'=>'PAY-' .$sale->id,
                    'debit'=>0,
                    'credit'=>$data['paid_amount'],
                ]);
            }
            return $sale->load('saleItems');

        });
    }

    public function getAll($filters)
    {
        $query = Sale::with('saleItems');
        if(!empty($filters['customer_id'])){
            $query->where('customer_id',$filters['customer_id']);
        }
        
        if(!empty($filters['from'])){
            $query->whereDate('created_at','>=',$filters['from']);
        }
        if(!empty($filters['to'])){
            $query->whereDate('created_at','<=',$filters['to']);
        }
        
        return $query->latest()->paginate(15);
    }
    public function getById($id)
    {
        return Sale::with('saleItems')->findOrFail($id);
    }
}