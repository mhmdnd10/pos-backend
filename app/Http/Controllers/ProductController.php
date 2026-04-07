<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;





class ProductController extends Controller
{
    protected $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    public function index(Request $request)
    {
        $search=trim($request->query('search'));
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->getAll($search, $perPage);
        return response()->json($products, 200);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock_quantity' => 'required|numberic',
            'image' => 'nullable|image|max:2048',
            'size' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'minimum_stock_alert' => 'integer|min:0',
        ]);
        if($request->hasFile('image')){
            $data['image'] = $request->file('image');
        }
        $product = $this->productService->create($data);
        return response()->json($product, 201);
    }
    public function show($id)
    {
        $product = $this->productService->getById($id);
        return response()->json($product, 200);
    }
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'description' => 'nullable|string',
            'purchase_price' => 'sometimes|required|numeric',
            'sale_price' => 'sometimes|required|numeric',
            'stock_quantity' => 'sometimes|required|numeric',
            'image' => 'nullable|image|max:2048',
            'size' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'minimum_stock_alert' => 'integer|min:0',
        ]);
        if($request->hasFile('image')){
            $data['image'] = $request->file('image');
        }
        $product = $this->productService->getById($id);
        $updatedProduct = $this->productService->update($product, $data);
        return response()->json($updatedProduct, 200);

    }
    public function destroy($id)
    {
        $product = $this->productService->getById($id);
        $this->productService->delete($product);
        return response()->json(['message' => 'Product deleted successfully'], 200);
    }
    public function getByCategory($categoryId, Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $search = trim($request->query('search'));
        $products = $this->productService->getByCategory($categoryId, $perPage, $search);
        return response()->json($products, 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);
        $shopId = $request->user()->shop_id;
        Excel::queueImport(new ProductsImport($shopId), $request->file('file'));
        return response()->json(['message' => 'Products imported successfully'], 200);
    }

}
