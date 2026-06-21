<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuestPostOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestPostController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => GuestPostOrder::all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'article_title' => 'required|string|max:255',
            'target_url' => 'nullable|string',
            'anchor_text' => 'nullable|string',
        ]);

        $order = GuestPostOrder::create($validated);

        return response()->json([
            'message' => 'Guest post order created',
            'data' => $order,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,published',
        ]);

        $order = GuestPostOrder::findOrFail((int) $id);
        $order->update($validated);

        return response()->json([
            'message' => 'Guest post order updated',
            'data' => $order,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        GuestPostOrder::findOrFail((int) $id)->delete();

        return response()->json([
            'message' => 'Guest post order deleted',
        ]);
    }
}
