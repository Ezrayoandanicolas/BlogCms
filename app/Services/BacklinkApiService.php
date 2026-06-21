<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BacklinkApiService
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('BACKLINK_API_URL', 'https://device.quailtv.org/api');
    }

    public function claim(string $articleSlug, string $articleDomain, int $limit = 2): array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->apiUrl}/backlink/claim", [
                    'article_slug' => $articleSlug,
                    'article_domain' => $articleDomain,
                    'limit' => $limit,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }
        } catch (\Exception $e) {
            logger()->error('Backlink claim failed: ' . $e->getMessage());
        }

        return [];
    }

    public function get(string $articleSlug, string $articleDomain): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->apiUrl}/backlink/get", [
                    'article_slug' => $articleSlug,
                    'article_domain' => $articleDomain,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? [];
            }
        } catch (\Exception $e) {
            logger()->error('Backlink get failed: ' . $e->getMessage());
        }

        return [];
    }

    public function claimAndGet(string $articleSlug, string $articleDomain, int $limit = 2): array
    {
        $this->claim($articleSlug, $articleDomain, $limit);
        return $this->get($articleSlug, $articleDomain);
    }

    public function fetchDomain(string $url): string
    {
        $parsed = parse_url($url);
        return $parsed['host'] ?? $url;
    }

    public function fetchTitle(string $url): ?string
    {
        try {
            $response = Http::timeout(5)->get($url);
            if ($response->successful()) {
                $html = $response->body();
                preg_match('/<title[^>]*>(.*?)<\/title>/si', $html, $matches);
                return trim($matches[1] ?? '');
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}
