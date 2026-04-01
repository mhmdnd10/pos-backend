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
    private function normalizeSearchValue(?string $search): string
    {
        if (!$search) {
            return '';
        }

        $normalized = trim(mb_strtolower($search));
        $normalized = strtr($normalized, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $normalized = str_replace(['/', '-', '_', '.', ',', ' '], '', $normalized);

        return $normalized;
    }

    private function normalizeSearchTokens(?string $search): array
    {
        if (!$search) {
            return [];
        }

        $normalized = trim(mb_strtolower($search));
        $normalized = strtr($normalized, [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
        $normalized = str_replace(['/', '-', '_', '.', ','], ' ', $normalized);
        $tokens = preg_split('/\s+/', $normalized, -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_unique($tokens));
    }

    private function normalizeTokenForLike(string $token): string
    {
        return str_replace(['/', '-', '_', '.', ',', ' '], '', trim(mb_strtolower($token)));
    }

    private function buildTokenVariants(string $token): array
    {
        $base = $this->normalizeTokenForLike($token);
        if ($base === '') {
            return [];
        }

        $folded = strtr($base, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ة' => 'ه',
            'ى' => 'ي',
        ]);

        return array_values(array_unique(array_filter([$base, $folded])));
    }

    private function applyFlexibleSearch($query, ?string $search): void
    {
        $tokens = $this->normalizeSearchTokens($search);

        if (empty($tokens)) {
            return;
        }

        $normalizedConcat = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT_WS(' ', name, size, brand, color, barcode), '/', ''), '-', ''), '_', ''), '.', ''), ',', ''), ' ', ''))";

        foreach ($tokens as $token) {
            $variants = $this->buildTokenVariants($token);
            if (empty($variants)) {
                continue;
            }

            $query->where(function ($tokenQuery) use ($variants, $normalizedConcat) {
                foreach ($variants as $variant) {
                    $likeToken = '%' . $variant . '%';

                    $tokenQuery->orWhere(function ($variantQuery) use ($likeToken, $normalizedConcat, $variant) {
                        $variantQuery->where('name', 'like', $likeToken)
                            ->orWhere('brand', 'like', $likeToken)
                            ->orWhere('color', 'like', $likeToken)
                            ->orWhere('size', 'like', $likeToken)
                            ->orWhere('barcode', 'like', $likeToken)
                            ->orWhereRaw($normalizedConcat . ' like ?', ['%' . $variant . '%']);
                    });
                }
            });
        }
    }

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
    public function getAll($search=null,$perPage=15){
        return Product::query()
            ->when($search, function($query) use ($search){
                $this->applyFlexibleSearch($query, $search);
            })->with('category')->orderBy('id', 'desc')->paginate($perPage);
    }
    public function getById($id){
        return Product::findOrFail($id);
    }
    public function getByCategory($categoryId,$perPage=15, $search=null){
        return Product::query()->where('category_id', $categoryId)
            ->when($search, function($query) use ($search){
                $this->applyFlexibleSearch($query, $search);
            })->paginate($perPage);
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