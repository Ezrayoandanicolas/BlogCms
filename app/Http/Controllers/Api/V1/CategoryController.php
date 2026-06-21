<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => CategoryResource::collection($this->categoryService->all()),
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'data' => new CategoryResource($this->categoryService->findById((int) $id)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $category = $this->categoryService->create($validated);

        return response()->json([
            'message' => 'Category created',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $category = $this->categoryService->update((int) $id, $validated);

        return response()->json([
            'message' => 'Category updated',
            'data' => new CategoryResource($category),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->categoryService->delete((int) $id);

        return response()->json([
            'message' => 'Category deleted',
        ]);
    }
}
