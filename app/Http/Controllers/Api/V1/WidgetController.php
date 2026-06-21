<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WidgetResource;
use App\Models\Widget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => WidgetResource::collection(Widget::all()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'title' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $widget = Widget::create($validated);

        return response()->json([
            'message' => 'Widget created',
            'data' => new WidgetResource($widget),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'title' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);

        $widget = Widget::findOrFail((int) $id);
        $widget->update($validated);

        return response()->json([
            'message' => 'Widget updated',
            'data' => new WidgetResource($widget),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        Widget::findOrFail((int) $id)->delete();

        return response()->json([
            'message' => 'Widget deleted',
        ]);
    }
}
