<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SaleService;


class SaleController extends Controller
{
    protected $saleService;
    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'total_amount' => 'required|numeric',
            'paid_amount' => 'required|numeric',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);
        $sale = $this->saleService->create($data);
        return response()->json($sale, 201);
    }
    public function index(Request $request)
    {
        $filters=$request->only(['customer_id','from','to']);
        $sales = $this->saleService->getAll($filters);
        return response()->json($sales,200);
    }
    public function show($id)
    {
        $sale = $this->saleService->getById($id);
        return response()->json($sale,200);
    }
}
