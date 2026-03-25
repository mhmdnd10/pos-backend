<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SupplierPaymentService;


class SupplierPaymentController extends Controller
{
    protected $supplierPaymentService;
    public function __construct(SupplierPaymentService $supplierPaymentService)
    {
        $this->supplierPaymentService = $supplierPaymentService;
    }
    public function store(Request $request)
    {
        $data=$request->validate([
            'supplier_id'=>'required|exists:suppliers,id',
            'purchase_id'=>'nullable|exists:purchases,id',
            'amount'=>'required|numeric',
            'payment_method'=>'required|string',
            'reference_number'=>'nullable|string',
            'notes'=>'nullable|string',
            'payment_date'=>'nullable|date',
        ]);
        $payment=$this->supplierPaymentService->create($data);
        return response()->json([
            'message'=>'Payment recorded successfully',
            'payment'=>$payment
        ],201);
    }

    public function index()
    {
        $payments=$this->supplierPaymentService->getAll();
        return response()->json($payments,200);
    }
    public function show($id)
    {
        $payment=$this->supplierPaymentService->getById($id);
        return response()->json($payment,200);
    }
}
