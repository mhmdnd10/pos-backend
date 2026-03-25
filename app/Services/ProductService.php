<?php
namespace App\Services;
use App\Models\Product;
// use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\Category;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;



class ProductService{
    public function create(array $data){
        if(isset($data['image'])){
            $file=$data['image'];
           $manager=new ImageManager(new Driver());
           $image=$manager->read($file)->resize(800,null)->tojpeg(80);
            $filename=uniqid().'.jpg';
            Storage::disk('public')->put('products/'.$filename,(string) $image);
            $data['image']='products/'.$filename;
        }
        
        return Product::create($data);
    }
    public function getAll($search=null){
        return Product::when($search, function($query) use ($search){
            $query->where('name','Like', $search .'%')
                  ->orWhere('barcode','Like', $search .'%');
                  
        })->with('category')->orderBy('id', 'desc')->paginate(15);
    }
    public function getById($id){
        return Product::findOrFail($id);
    }
    public function getByCategory($categoryId){
        return Product::where('category_id', $categoryId)->paginate(15);
    }
    public function update(Product $product, array $data){
       if(isset($data['image'])){
        if($product->image && Storage::disk('public')->exists($product->image)){
            Storage::disk('public')->delete($product->image);
        }
        $file=$data['image'];
        $manager=new ImageManager(new Driver());
        $image=$manager->read($file)->resize(800,null)->tojpeg(80);
        $filename=uniqid().'.jpg';
        Storage::disk('public')->put('products/'.$filename,(string) $image);
        $data['image']='products/'.$filename;
       }
         $product->update($data);
            return $product;
    }
    public function delete(Product $product){
        if($product->image && Storage::disk('public')->exists($product->image)){
            Storage::disk('public')->delete($product->image);
        }
        $product->delete();
    }
}