<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CustomerService;



class CustomerController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        $customer = $this->customerService->create($data);
        return response()->json($customer, 201);
    }
    public function index(Request $request)
    {
        $search = $request->query('search');
        $customers = $this->customerService->getAll($search);
        return response()->json($customers,200);
    }
    public function show($id)
    {
        $customer = $this->customerService->getById($id);
        return response()->json($customer,200);
    }
    public function update(Request $request, $id)
    {
        $customer = $this->customerService->getById($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        $updatedCustomer = $this->customerService->update($customer, $data);
        return response()->json($updatedCustomer, 200);
    }
    public function destroy($id)
    {
        $customer = $this->customerService->getById($id);
        $this->customerService->delete($customer);
        return response()->json(null, 204);
    }
    public function ledger($id)
    {
        $ledgerEntries = $this->customerService->getLedger($id);
        return response()->json($ledgerEntries, 200);
    }
}
