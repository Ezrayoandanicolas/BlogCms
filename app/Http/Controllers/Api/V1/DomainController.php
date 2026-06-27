<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DomainTheme;
use App\Models\Post;
use App\Models\Theme;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(): JsonResponse
    {
        $domains = DomainTheme::where('active', true)->orderBy('domain')->get();

        $data = $domains->map(function ($domain) {
            $isLocal = str_contains($domain->domain, 'localhost') || !str_contains($domain->domain, '.');
            $url = ($isLocal ? 'http://' : 'https://') . $domain->domain;
            $articlesCount = Post::withoutTenant()
                ->where('domain_id', $domain->id)
                ->where('status', 'published')
                ->count();
            $futureCount = Post::withoutTenant()
                ->where('domain_id', $domain->id)
                ->where('status', 'published')
                ->where('published_at', '>', now())
                ->count();

            return [
                'id' => $domain->id,
                'host' => $domain->domain,
                'url' => $url,
                'active' => $domain->active,
                'theme_slug' => $domain->theme_slug,
                'articles_count' => $articlesCount,
                'future_scheduled' => $futureCount,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'domains' => 'required|array|min:1',
            'domains.*' => 'required|string|max:255',
        ]);

        $themes = Theme::where('status', true)->pluck('slug')->toArray();

        if (empty($themes)) {
            return response()->json(['message' => 'No active themes available'], 400);
        }

        $existing = DomainTheme::whereIn('domain', $data['domains'])->pluck('domain')->toArray();
        $created = [];

        foreach ($data['domains'] as $domain) {
            if (in_array($domain, $existing)) {
                continue;
            }

            $themeSlug = $themes[array_rand($themes)];

            $domainTheme = DomainTheme::create([
                'domain' => $domain,
                'theme_slug' => $themeSlug,
                'active' => true,
            ]);

            $created[] = [
                'id' => $domainTheme->id,
                'host' => $domainTheme->domain,
                'theme_slug' => $domainTheme->theme_slug,
                'active' => $domainTheme->active,
            ];
        }

        $message = count($created) > 0
            ? count($created) . ' domain(s) created successfully'
            : 'All domains already exist';

        return response()->json([
            'message' => $message,
            'data' => $created,
        ], count($created) > 0 ? 201 : 200);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $domain = DomainTheme::findOrFail($id);

        $data = $request->validate([
            'domain' => 'sometimes|required|string|max:255|unique:domain_themes,domain,' . $id,
            'theme_slug' => 'sometimes|required|exists:themes,slug',
            'active' => 'sometimes|boolean',
        ]);

        $domain->update($data);

        return response()->json([
            'message' => 'Domain updated successfully',
            'data' => [
                'id' => $domain->id,
                'host' => $domain->domain,
                'theme_slug' => $domain->theme_slug,
                'active' => $domain->active,
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $domain = DomainTheme::findOrFail($id);
        $domain->delete();

        return response()->json(['message' => 'Domain deleted successfully']);
    }
}
