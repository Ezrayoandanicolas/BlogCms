<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ThemeResource;
use App\Models\ThemeSetting;
use App\Services\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(
        protected ThemeService $themeService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ThemeResource::collection($this->themeService->all()),
        ]);
    }

    public function activate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|exists:themes,id',
        ]);

        $theme = $this->themeService->activate((int) $validated['id']);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'active_theme'],
            ['value' => $theme->slug]
        );

        return response()->json([
            'message' => 'Theme activated',
            'data' => new ThemeResource($theme),
        ]);
    }

    public function settings(): JsonResponse
    {
        $activeTheme = \App\Models\Setting::where('key', 'active_theme')->value('value');
        $settings = ThemeSetting::where('theme', $activeTheme)->get();

        return response()->json([
            'data' => $settings,
        ]);
    }

    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        $activeTheme = \App\Models\Setting::where('key', 'active_theme')->value('value') ?? 'default';

        foreach ($validated['settings'] as $setting) {
            ThemeSetting::updateOrCreate(
                ['theme' => $activeTheme, 'key' => $setting['key']],
                ['value' => $setting['value'] ?? '']
            );
        }

        return response()->json([
            'message' => 'Theme settings updated',
        ]);
    }
}
