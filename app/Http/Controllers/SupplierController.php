<?php

namespace App\Http\Controllers;
use App\Services\SupplierService;


use Illuminate\Http\Request;

class SupplierController extends Controller
{
    protected $supplierService;
    public function __construct(SupplierService $supplierService){
        $this->supplierService=$supplierService;
    }
    public function index(Request $request){
        $search=$request->input('search');
        $suppliers=$this->supplierService->getAll($search);
        return response()->json($suppliers,200);
    }
    public function store(Request $request){
        $data=$request->validate([
            'name'=>'required|string|max:255|unique:suppliers,name',
            'phone'=>'required|string|max:20',
            'address'=>'nullable|string|max:255',
            'current_balance'=>'nullable|numeric',
        ]);
        $supplier=$this->supplierService->create($data);
        return response()->json($supplier,201);
    }
    public function show($id){
        $supplier=$this->supplierService->getById($id);
        return response()->json($supplier,200);
    }
    public function update(Request $request, $id){
        $supplier=$this->supplierService->getById($id);
        $data=$request->validate([
            'name'=>'sometimes|required|string|max:255|unique:suppliers,name,'.$supplier->id,
            'phone'=>'sometimes|required|string|max:20',
            'address'=>'nullable|string|max:255',
            'current_balance'=>'nullable|numeric',
        ]);
        $updatedSupplier=$this->supplierService->update($supplier, $data);
        return response()->json($updatedSupplier,200);
    }
    public function destroy($id){
        $supplier=$this->supplierService->getById($id);
        $this->supplierService->delete($supplier);
        return response()->json([
            'message'=>'Supplier deleted successfully'

        ],200);
    }
    // public function updateBalance(Request $request, $id){
    //     $supplier=$this->supplierService->getById($id);
    //     $data=$request->validate([
    //         'current_balance'=>'required|numeric',
    //     ]);
    //     $this->supplierService->updateBalance($supplier, $data['current_balance']);
    //     return response()->json([
    //         'message'=>'Supplier balance updated successfully',
    //         'current_balance'=>$supplier->current_balance
    //     ],200);
    // }

   


}
