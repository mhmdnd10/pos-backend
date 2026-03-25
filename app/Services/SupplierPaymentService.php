<?php
namespace App\Services;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;


class SupplierPaymentService
{
    public function create(array $data)
    {
        return DB::transaction(function() use($data){
              $payment=SupplierPayment::create($data);
              SupplierLedger::create([
                  'supplier_id'=>$data['supplier_id'],
                  'type'=>'payment',
                  'reference_id'=>$payment->id,
                  'reference_number'=>'PAY-' . $payment->id,
                  'debit'=>$data['amount'],
                  'credit'=>0,
                  'payment_date'=>$data['payment_date'] ?? now(),
              ]);
                if(!empty($data['purchase_id'])){
                    $purchase=Purchase::findOrFail($data['purchase_id']);
                    $purchase->increment('paid_amount',$data['amount']);
                    $purchase->decrement('remaining_amount',$data['amount']);
                    $purchase->payment_status=$purchase->remaining_amount <= 0 ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid');
                    $purchase->save();
                }
                return $payment->load('supplier','purchase');
        });
    }

    public function getAll()
    {
        return SupplierPayment::with('supplier','purchase')->paginate(15);
    }

    public function getById($id){
        return SupplierPayment::with('supplier','purchase')->findOrFail($id);
    }
}