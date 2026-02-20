<?php
namespace App\Services;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;



class SupplierService
{
    public function create(array $data){
        return Supplier::create($data);
    }
    public function getAll($search=null){
        return Supplier::when($search, function($query) use ($search){
            $query->where('name','Like', $search .'%');

        })->paginate(15);
    }
    public function getById($id){
        return Supplier::with('ledgers')->findOrFail($id);
    }
    public function update(Supplier $supplier, array $data){
        $supplier->update($data);
        return $supplier;
    }
    public function delete(Supplier $supplier){
        $supplier->delete();
    }

    // public function updateBalance(Supplier $supplier, $amount){
    //     return DB::transaction(function() use ($supplier, $amount){
    //         $supplier->increment('current_balance', $amount);
    //         return $supplier->fresh();
    //     });
       
    // }

   
     
}