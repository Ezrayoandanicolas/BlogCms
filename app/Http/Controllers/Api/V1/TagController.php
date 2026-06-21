<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Services\TagService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function __construct(
        protected TagService $tagService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => TagResource::collection($this->tagService->all()),
        ]);
    }

    public function show($id): JsonResponse
    {
        return response()->json([
            'data' => new TagResource($this->tagService->findById((int) $id)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = $this->tagService->create($validated);

        return response()->json([
            'message' => 'Tag created',
            'data' => new TagResource($tag),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = $this->tagService->update((int) $id, $validated);

        return response()->json([
            'message' => 'Tag updated',
            'data' => new TagResource($tag),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $this->tagService->delete((int) $id);

        return response()->json([
            'message' => 'Tag deleted',
        ]);
    }
}
