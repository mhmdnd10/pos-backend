<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PurchaseService;

class PurchaseController extends Controller
{
    protected $purchaseService;
    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }
    public function store(Request $request)
    {
        $data=$request->validate([
            'supplier_id'=>'required|exists:suppliers,id',
            'total_amount'=>'required|numeric',
            'paid_amount'=>'required|numeric',
            'items'=>'required|array',
            'items.*.product_id'=>'required|exists:products,id',
            'items.*.quantity'=>'required|numeric|min:1',
            'items.*.unit_price'=>'required|numeric|min:0',
        ]);
        $purchase=$this->purchaseService->create($data);
        return response()->json($purchase,201);
    }

    public function index(Request $request)
    {
        $filters=$request->only(['supplier_id','from','to']);
        $purchases=$this->purchaseService->getAll($filters);
        return response()->json($purchases,200);
    }
    public function show($id)
    {
        $purchase=$this->purchaseService->getById($id);
        return response()->json($purchase,200);
    }
}
