<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Category\CategoryIndexResource;
use App\Http\Resources\Product\ProductIndexResource;
use App\Models\Category\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{


    public function index()
    {
        $categories = Category::where('status', true)->with('children', 'children.children')->parents()->get();

       return CategoryIndexResource::collection($categories);
    }



    public function show(Category $category)
    {
        $category->load('children', 'children.children');
        $productsPaginated = $category->products()->with('flat')->orderByDesc('view_count')->paginate(12);

        return ProductIndexResource::collection($productsPaginated)->additional(['category' => new CategoryIndexResource($category)]);
    }




}