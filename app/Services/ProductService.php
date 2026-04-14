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
   private function normalize($text){
        $text=mb_strtolower($text,'UTF-8');
        $text=strtr($text,[
            'أ'=>'ا','إ'=>'ا','آ'=>'ا',
            'ة'=>'ه','ى'=>'ي'
        ]);
         $text=str_replace(['/', '-', '_', '.', ',', ' '], '', $text);
        return $text;
   }

    private function applySearch($query,?string $search){
        if(!$search) return;
        $tokens= explode(' ', trim($search));
        foreach($tokens as $token){
            $token=$this->normalize($token);
            if($token === '') continue;
            $query->where('search_text','like',"%$token%");
        }
    }
    public function create(array $data){
        $data['search_text']=$this->normalize(
            ($data['name'] ?? '') .
            ($data['brand'] ?? '') .
            ($data['color'] ?? '') .
            ($data['size'] ?? '') .
            ($data['barcode'] ?? '') .
            ($data['model'] ?? '') .
            ($data['shelf_location'] ?? '')

        );
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
    public function getAll($search=null,$perPage=15){
        return Product::query()
            ->when($search, function($query) use ($search){
                $this->applySearch($query, $search);
            })->with('category')->orderBy('id', 'desc')->paginate($perPage);
    }
    public function getById($id){
        return Product::findOrFail($id);
    }
    public function getByFilter($filters,$perPage=15, $search=null){
        $query=Product::query();
        $filters=['category_id','shelf_location','brand','size','color'];
        foreach($filters as $filter){
            if(request()->has($filter)){
                $query->where($filter, request()->query($filter));
            }
        }
        
         return $query->when($search, function($query) use ($search){
                $this->applySearch($query, $search);
            })->with('category')->orderBy('id', 'desc')->paginate($perPage);
         
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
        $data['search_text']=$this->normalize(
            ($data['name'] ?? $product->name) .
            ($data['brand'] ?? $product->brand) .
            ($data['color'] ?? $product->color) .
            ($data['size'] ?? $product->size) .
            ($data['barcode'] ?? $product->barcode) .
            ($data['model'] ?? $product->model) .
            ($data['shelf_location'] ?? $product->shelf_location)
        );
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