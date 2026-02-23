<?php
namespace App\Services;
use App\Models\Customer;

class CustomerService
{
    public function create(array $data){
        return Customer::create($data);
    }
    public function getAll($search=null){
        return Customer::when($search, function($query) use ($search){
            $query->where('name','Like', $search .'%')
                  ->orWhere('phone','Like', $search .'%');

        })->paginate(15);
    }
    public function getById($id){
        return Customer::with('ledgers')->findOrFail($id);
    }
    public function update(Customer $customer, array $data){
        $customer->update($data);
        return $customer;
    }
    public function delete(Customer $customer){
        $customer->delete();
    }

    public function getLedger($customerId){
        $customer=Customer::with('ledgers')->findOrFail($customerId);
        return $customer->ledgers()->orderBy('created_at','desc')->paginate(15);
    }

   
     
}
