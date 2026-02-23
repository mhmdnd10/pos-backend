<?php
namespace App\Services;
use App\Models\Sale;
use App\Models\CustomerPayment;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\DB;


class CustomerPaymentService
{
    public function create(array $data)
    {
        return DB::transaction(function() use ($data){
            $payment=CustomerPayment::create($data);
            CustomerLedger::create([
                'customer_id'=>$data['customer_id'],
                'type'=>'payment',
                'reference_id'=>$payment->id,
                'reference_number'=>'PAY-' . $payment->id,
                'debit'=>0,
                'credit'=>$data['amount'],
            ]);
            if(!empty($data['sale_id'])){
                $sale=Sale::findOrFail($data['sale_id']);
                $sale->increment('paid_amount',$data['amount']);
                $sale->decrement('remaining_amount',$data['amount']);
                $sale->payment_status=$sale->remaining_amount <= 0 ? 'paid' : ($sale->paid_amount > 0 ? 'partial' : 'unpaid');
                $sale->save();
            }      
            return $payment->load('customer','sale');
        });
    }

    public function getAll()
    {
        return CustomerPayment::with('customer','sale')->paginate(15);
    }
    public function getById($id)
    {
        return CustomerPayment::with('customer','sale')->findOrFail($id);
    }
}