<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading, ShouldQueue
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $shopId;
    public function __construct($shopId)
    {       
         $this->shopId = $shopId;
    }
    public function chunkSize(): int
    {
        return 1000;
    }
    public function model(array $row)
    {
        $imagePath = null;
        if(!empty($row['image_url'])){
            $response=Http::get($row['image_url']);
            if($response->successful()){
                $manager=new ImageManager(new Driver());
                $image=$manager->read($response->body())->resize(800,null)->tojpeg(80);
                $filename=uniqid().'.jpg';
                Storage::disk('public')->put('products/'.$filename,(string) $image);
                $imagePath='products/'.$filename;
            }

        }
        return new Product([
            'name' => $row['name'],
            'barcode' => $row['barcode'] ?? null,
            'category_id' => $row['category_id'],
            'description' => $row['description'] ?? null,
            'purchase_price' => $row['purchase_price'],
            'sale_price' => $row['sale_price'],
            'stock_quantity' => $row['stock_quantity'],
            'image' => $imagePath,
            'shop_id' => $this->shopId,
            'is_active' => $row['is_active'] ?? true,
            'minimum_stock_alert' => $row['minimum_stock_alert'] ?? 0,
        ]);
    }
}
