<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Backlink;
use App\Models\BacklinkSite;
use App\Services\BacklinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BacklinkController extends Controller
{
    public function __construct(
        protected BacklinkService $backlinkService
    ) {}

    public function sites(): JsonResponse
    {
        return response()->json([
            'data' => BacklinkSite::withCount('backlinks')->get(),
        ]);
    }

    public function storeSite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $site = BacklinkSite::create($validated);

        return response()->json([
            'message' => 'Backlink site created',
            'data' => $site,
        ], 201);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->backlinkService->all(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:backlink_sites,id',
            'post_id' => 'nullable|exists:posts,id',
            'anchor_text' => 'nullable|string',
            'target_url' => 'required|string',
            'link_type' => 'nullable|in:dofollow,nofollow,ugc,sponsored',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        $backlink = Backlink::create($validated);

        return response()->json([
            'message' => 'Backlink created',
            'data' => $backlink,
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|exists:backlink_sites,id',
            'post_id' => 'nullable|exists:posts,id',
            'anchor_text' => 'nullable|string',
            'target_url' => 'required|string',
            'link_type' => 'nullable|in:dofollow,nofollow,ugc,sponsored',
            'status' => 'nullable|in:active,inactive,pending',
        ]);

        $backlink = Backlink::findOrFail((int) $id);
        $backlink->update($validated);

        return response()->json([
            'message' => 'Backlink updated',
            'data' => $backlink,
        ]);
    }

    public function destroy($id): JsonResponse
    {
        Backlink::findOrFail((int) $id)->delete();

        return response()->json([
            'message' => 'Backlink deleted',
        ]);
    }

    public function check(int $id): JsonResponse
    {
        $backlink = Backlink::findOrFail($id);
        $backlink->logs()->create([
            'checked_at' => now(),
        ]);

        return response()->json([
            'message' => 'Backlink check queued',
        ]);
    }
}
