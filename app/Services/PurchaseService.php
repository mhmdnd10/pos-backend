<?php
namespace App\Services;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;


class PurchaseService
{
    public function create(array $data)
    {
        return DB::transaction(function() use ($data){
            $purchase=Purchase::create([
                'supplier_id'=>$data['supplier_id'],
                'total_amount'=>$data['total_amount'],
                'paid_amount'=>$data['paid_amount'],
                'remaining_amount'=>$data['total_amount'] - $data['paid_amount'],
            ]);
            foreach($data['items'] as $item){
               $itemModel= $purchase->purchaseItems()->create([
                    'product_id'=>$item['product_id'],
                    'quantity'=>$item['quantity'],
                    'price'=>$item['price'],
                ]);
                $itemModel->product()->increment('stock_quantity',$item['quantity']);
            }
            SupplierLedger::create([
                'supplier_id'=>$data['supplier_id'],
                'type'=>'purchase',
                'reference_id'=>$purchase->id,
                'refernce_number'=>'PUR-' .$purchase->id,
                'debit'=>0,
                'credit'=>$data['total_amount'],
            ]);
            if($data['paid_amount'] > 0){
                SupplierLedger::create([
                    'supplier_id'=>$data['supplier_id'],
                    'type'=>'payment',
                    'reference_id'=>$purchase->id,
                    'refernce_number'=>'PAY-' .$purchase->id,
                    'debit'=>$data['paid_amount'],
                    'credit'=>0,
                ]);
            }
            return $purchase->load('purchaseItems');
        });
    }

    public function getAll()
    {
        return Purchase::with('purchaseItems')->paginate(15);
    }

    public function getById($id)
    {
        return Purchase::with('purchaseItems')->findOrFail($id);
    }

}