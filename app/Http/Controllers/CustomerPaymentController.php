<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerPaymentService;



class CustomerPaymentController extends Controller
{
    protected $customerPaymentService;
    public function __construct(CustomerPaymentService $customerPaymentService)
    {
        $this->customerPaymentService = $customerPaymentService;
    }
    public function store(Request $request)
    {
        $data=$request->validate([
            'customer_id'=>'required|exists:customers,id',
            'sale_id'=>'nullable|exists:sales,id',
            'amount'=>'required|numeric',
            'payment_method'=>'required|string',
            'reference_number'=>'nullable|string',
            'notes'=>'nullable|string',
            'payment_date'=>'nullable|date',
        ]);
        $payment=$this->customerPaymentService->create($data);
        return response()->json([
            'message'=>'Payment recorded successfully',
            'payment'=>$payment
        ],201);
    }

    public function index()
    {
        $payments=$this->customerPaymentService->getAll();
        return response()->json($payments,200);
    }
    public function show($id)
    {
        $payment=$this->customerPaymentService->getById($id);
        return response()->json($payment,200);
    }

}
