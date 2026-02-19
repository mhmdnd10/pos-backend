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
    public function index()
    {
        $products = $this->productService->getAll();
        return response()->json($products, 200);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'purchase_price' => 'required|numeric',
            'sale_price' => 'required|numeric',
            'stock_quantity' => 'required|integer',
            'image' => 'nullable|image|max:2048',
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
            'category_id' => 'sometimes|required|exists:categories,id',    
            'description' => 'nullable|string',
            'purchase_price' => 'sometimes|required|numeric',
            'sale_price' => 'sometimes|required|numeric',
            'stock_quantity' => 'sometimes|required|integer',
            'image' => 'nullable|image|max:2048',
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
    public function getByCategory($categoryId)
    {
        $products = $this->productService->getByCategory($categoryId);
        return response()->json($products, 200);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);
        Excel::queueImport(new ProductsImport, $request->file('file'));
        return response()->json(['message' => 'Products imported successfully'], 200);
    }

}
