<?php
namespace App\Services;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\DB;
use App\Models\Product;




class SaleService
{
    public function create(array $data)
    {
        return DB::transaction(function() use ($data){
            $sale=Sale::create([
                'customer_id'=>$data['customer_id'] ?? null,
                'total_amount'=>$data['total_amount'],
                'paid_amount'=>$data['paid_amount'],
                'remaining_amount'=>$data['total_amount'] - $data['paid_amount'],
                'payment_status'=>$data['paid_amount'] >= $data['total_amount'] ? 'paid' : ($data['paid_amount'] > 0 ? 'partial' : 'unpaid'),
                'sale_date'=>$data['sale_date'] ?? now(),
                'invoice_status'=>'finalized',
            ]);
            $sale->invoice_number = $this->generateInvoiceNumber($sale->id);
            $sale->save();
            $data['shop_id'] = $sale->shop_id; 
            foreach($data['items'] as $item){
               $itemModel= $sale->saleItems()->create([
                    'product_id'=>$item['product_id'],
                    'quantity'=>$item['quantity'],
                    'unit_price'=>$item['unit_price'],
                    'total_price'=>$item['quantity'] * $item['unit_price'],
                ]);
                $itemModel->product()->decrement('stock_quantity',$item['quantity']);
            }
            if (!empty($data['customer_id'])) {
                CustomerLedger::create([
                    'customer_id'=>$data['customer_id'],
                    'type'=>'sale',
                    'reference_id'=>$sale->id,
                    'reference_number'=>'SAL-' .$sale->id,
                    'debit'=>$data['total_amount'],
                    'credit'=>0,
                ]);

                if($data['paid_amount'] > 0){
                    CustomerLedger::create([
                        'customer_id'=>$data['customer_id'],
                        'type'=>'payment',
                        'reference_id'=>$sale->id,
                        'reference_number'=>'PAY-' .$sale->id,
                        'debit'=>0,
                        'credit'=>$data['paid_amount'],
                    ]);
                }
            }
            return $sale->load('saleItems');

        });
    }

    public function getAll($filters)
    {
        $query = Sale::with('saleItems');
        if(!empty($filters['customer_id'])){
            $query->where('customer_id',$filters['customer_id']);
        }
        
        if(!empty($filters['from'])){
            $query->whereDate('created_at','>=',$filters['from']);
        }
        if(!empty($filters['to'])){
            $query->whereDate('created_at','<=',$filters['to']);
        }
        
        return $query->latest()->paginate(15);
    }
    public function getById($id)
    {
        return Sale::with('saleItems')->findOrFail($id);
    }

    public function generateInvoiceNumber($saleId)
    {
        return 'INV-' . str_pad($saleId, 6, '0', STR_PAD_LEFT);
    }

    public function getInvoiceNumber($saleId)
    {
        $sale = Sale::findOrFail($saleId);
        return $sale->invoice_number;
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $sale = Sale::findOrFail($id);
            $oldItems = $sale->saleItems()->get()->keyBy('product_id');
            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $newQty = $item['quantity'];
                $total += $newQty * $item['unit_price'];

                if (isset($oldItems[$item['product_id']])) {
                    $oldItem = $oldItems[$item['product_id']];
                    $oldQty = $oldItem->quantity;
                    $diff = $newQty - $oldQty;

                    if ($diff > 0) {
                        if ($product->stock_quantity < $diff) {
                            throw new \Exception("Not enough stock for product ID: {$item['product_id']}");
                        }
                        $product->decrement('stock_quantity', $diff);
                    } else if ($diff < 0) {
                        $product->increment('stock_quantity', abs($diff));
                    }

                    $oldItem->update([
                        'quantity' => $newQty,
                        'unit_price' => $item['unit_price'],
                        'total_price' => $newQty * $item['unit_price'],
                    ]);
                    unset($oldItems[$item['product_id']]);
                } else {
                    if ($product->stock_quantity < $newQty) {
                        throw new \Exception("Not enough stock for product ID: {$item['product_id']}");
                    }
                    $sale->saleItems()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $newQty,
                        'unit_price' => $item['unit_price'],
                        'total_price' => $newQty * $item['unit_price'],
                    ]);
                    $product->decrement('stock_quantity', $newQty);
                }
            }

            foreach ($oldItems as $oldItem) {
                $product = Product::findOrFail($oldItem->product_id);
                $product->increment('stock_quantity', $oldItem->quantity);
                $oldItem->delete();
            }

            $sale->update([
                'total_amount' => $total,
                'paid_amount' => $data['paid_amount'],
                'remaining_amount' => $total - $data['paid_amount'],
                'payment_status' => $data['paid_amount'] >= $total ? 'paid' : ($data['paid_amount'] > 0 ? 'partial' : 'unpaid'),
            ]);

            return $sale->load('saleItems.product', 'customer');
        });
    }

    public function quickReturn(array $items)
{
    return DB::transaction(function () use ($items) {
        $totalReturnAmount = 0;
        $returnItems = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);

            $qty = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];

            if ($qty <= 0) {
                throw new \Exception("Invalid return quantity for product ID: {$item['product_id']}");
            }

          
            $product->increment('stock_quantity', $qty);

            $returnAmount = $qty * $unitPrice;
            $totalReturnAmount += $returnAmount;

            $returnItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'purchase_price' => $product->purchase_price,
                'total_price' => $returnAmount,
            ];
        }

        return [
            'message' => 'Return processed successfully',
            'total_return_amount' => $totalReturnAmount,
            'items' => $returnItems,
        ];
    });
}


    
}