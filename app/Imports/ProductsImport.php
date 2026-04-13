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
use App\Services\ProductService;

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
    private function normalize($text)
    {
        $text = mb_strtolower($text, 'UTF-8');

        $text = strtr($text, [
            'أ'=>'ا','إ'=>'ا','آ'=>'ا',
            'ة'=>'ه','ى'=>'ي'
        ]);

        return str_replace(['/', '-', '_', '.', ',', ' '], '', $text);
    }
    public function model(array $row)
    {
        $name = trim($row['name'] ?? '');
        if ($name === '') {
            return null; 
        }
        $purchasePrice = is_numeric($row['purchase_price'] ?? null) ? $row['purchase_price'] : 0;
        $salePrice = is_numeric($row['sale_price'] ?? null) ? $row['sale_price'] : 0;
        $stock = is_numeric($row['stock_quantity'] ?? null) ? $row['stock_quantity'] : 0;
        $model=trim($row['model'] ?? '');
        $shelfLocation=trim($row['shelf_location'] ?? '');

        if ($purchasePrice < 0 || $salePrice < 0 || $stock < 0) {
            return null;
        }

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
        $searchText=$this->normalize(
            $name .
            ($row['brand'] ?? '') .
            ($row['color'] ?? '') .
            ($row['size'] ?? '') .
            ($row['barcode'] ?? '') .
            $model .
            $shelfLocation

        );
       
        return new Product([
            'name' => $row['name'],
            'barcode' => $row['barcode'] ?? null,
            'category_id' => $row['category_id'] ?? null,
            'description' => $row['description'] ?? null,
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'stock_quantity' => $stock,
            'size' => $row['size'] ?? null,
            'color' => $row['color'] ?? null,
            'brand' => $row['brand'] ?? null,
            'image' => $imagePath,
            'shop_id' => $this->shopId,
            'is_active' => $row['is_active'] ?? true,
            'minimum_stock_alert' => $row['minimum_stock_alert'] ?? 0,
            'model' => $row['model'] ?: null,
            'shelf_location' => $row['shelf_location'] ?: null,
            'search_text' => $searchText,


        ]);
    }
}
