<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\DashboardController;

//auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

//category routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);
});

//product routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/products/category/{categoryId}', [ProductController::class, 'getByCategory']);
    Route::post('/products/import', [ProductController::class, 'import']);
});

//supplier routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::post('/suppliers', [SupplierController::class, 'store']);
    Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
    Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
    Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);
    Route::get('/suppliers/{id}/ledger', [SupplierController::class, 'ledger']);
});

//purchase routes
Route::prefix('purchases')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [PurchaseController::class, 'index']);
    Route::post('/', [PurchaseController::class, 'store']);
    Route::get('/{id}', [PurchaseController::class, 'show']);
});

//sale routes
Route::prefix('sales')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SaleController::class, 'index']);
    Route::post('/', [SaleController::class, 'store']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::put('/{id}', [SaleController::class, 'update']);
    Route::get('/invoice/{saleId}', [SaleController::class, 'getInvoiceNumber']);
    Route::post('/quick-return', [SaleController::class, 'quickReturn']);
});

//customer routes
Route::prefix('customers')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CustomerController::class, 'index']);
    Route::post('/', [CustomerController::class, 'store']);
    Route::get('/{id}', [CustomerController::class, 'show']);
    Route::put('/{id}', [CustomerController::class, 'update']);
    Route::delete('/{id}', [CustomerController::class, 'destroy']);
    Route::get('/{id}/ledger', [CustomerController::class, 'ledger']);
});

//customer payment routes
Route::prefix('customer-payments')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CustomerPaymentController::class, 'index']);
    Route::post('/', [CustomerPaymentController::class, 'store']);
    Route::get('/{id}', [CustomerPaymentController::class, 'show']);
});

//supplier payment routes
Route::prefix('supplier-payments')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [SupplierPaymentController::class, 'index']);
    Route::post('/', [SupplierPaymentController::class, 'store']);
    Route::get('/{id}', [SupplierPaymentController::class, 'show']);
});

//dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth:sanctum');

