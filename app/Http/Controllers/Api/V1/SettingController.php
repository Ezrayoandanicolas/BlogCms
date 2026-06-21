<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => [
                'site_name' => Setting::where('key', 'site_name')->first()?->value ?? config('app.name'),
                'site_description' => Setting::where('key', 'site_description')->first()?->value ?? '',
                'site_topic' => Setting::where('key', 'site_topic')->first()?->value ?? '',
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domain_id' => 'required|integer|exists:domain_themes,id',
            'settings' => 'required|array',
            'settings.site_name' => 'required|string|max:255',
            'settings.site_description' => 'nullable|string|max:500',
            'settings.site_topic' => 'nullable|string|max:255',
        ]);

        $domainId = $data['domain_id'];
        $settings = $data['settings'];

        foreach (['site_name', 'site_description', 'site_topic'] as $key) {
            Setting::withoutTenant()->updateOrCreate(
                ['key' => $key, 'domain_id' => $domainId],
                ['value' => $settings[$key] ?? '']
            );
        }

        return response()->json([
            'message' => 'Settings updated successfully',
            'data' => [
                'domain_id' => (int) $domainId,
                'site_name' => $settings['site_name'],
                'site_description' => $settings['site_description'] ?? '',
                'site_topic' => $settings['site_topic'] ?? '',
            ],
        ]);
    }
}
