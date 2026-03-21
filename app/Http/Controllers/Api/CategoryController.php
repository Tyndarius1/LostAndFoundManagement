<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;

class CategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $categories = Category::latest()->get();

        return $this->successResponse(
            'Categories retrieved successfully.',
            CategoryResource::collection($categories)
        );
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());

        return $this->successResponse(
            'Category created successfully.',
            new CategoryResource($category),
            201
        );
    }

    public function show(Category $category)
    {
        return $this->successResponse(
            'Category retrieved successfully.',
            new CategoryResource($category)
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return $this->successResponse(
            'Category updated successfully.',
            new CategoryResource($category->fresh())
        );
    }

    public function destroy(Category $category)
    {
        $category->delete();

        return $this->successResponse(
            'Category deleted successfully.'
        );
    }
}