<?php
namespace App\Services;
use App\Models\Category;


class CategoryService{
    public function getCategories(){
        return Category::all();
    }
    public function createCategory( array $data){
        return Category::create([
            'name'=>$data['name'],
        ]);
    }
    public function updateCategory(Category $category, array $data){
        
        $category->update([
            'name'=>$data['name'],
        ]);
        return $category;
    }
    public function deleteCategory(Category $category){
        $category->delete();
    }
}