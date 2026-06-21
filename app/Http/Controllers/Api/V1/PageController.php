<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Services\PageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageService $pageService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => PageResource::collection($this->pageService->all()),
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'data' => new PageResource($this->pageService->findById((int) $id)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $page = $this->pageService->create($validated);

        return response()->json([
            'message' => 'Page created',
            'data' => new PageResource($page),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'seo_keywords' => 'nullable|string',
        ]);

        $page = $this->pageService->update((int) $id, $validated);

        return response()->json([
            'message' => 'Page updated',
            'data' => new PageResource($page),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->pageService->delete((int) $id);

        return response()->json([
            'message' => 'Page deleted',
        ]);
    }
}
