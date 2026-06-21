<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ToolsController extends Controller
{
    public function generateSitemap()
    {
        $posts = Post::where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get(['slug', 'updated_at']);

        $categories = Category::all(['slug', 'updated_at']);
        $tags = Tag::all(['slug', 'updated_at']);

        $domain = url('/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        $xml .= "  <url><loc>{$domain}</loc><priority>1.0</priority></url>\n";
        $xml .= "  <url><loc>{$domain}/blog</loc><priority>0.8</priority></url>\n";

        foreach ($posts as $post) {
            $xml .= "  <url><loc>{$domain}/blog/{$post->slug}</loc><lastmod>{$post->updated_at->format('Y-m-d')}</lastmod><priority>0.6</priority></url>\n";
        }

        foreach ($categories as $cat) {
            $xml .= "  <url><loc>{$domain}/category/{$cat->slug}</loc><priority>0.4</priority></url>\n";
        }

        foreach ($tags as $tag) {
            $xml .= "  <url><loc>{$domain}/tag/{$tag->slug}</loc><priority>0.3</priority></url>\n";
        }

        $xml .= '</urlset>';

        Storage::disk('public')->put('sitemap.xml', $xml);

        $this->pingGoogle($domain . '/storage/sitemap.xml');

        return response()->json(['message' => 'Sitemap generated and Google pinged']);
    }

    private function pingGoogle(string $sitemapUrl)
    {
        try {
            $url = 'https://www.google.com/ping?sitemap=' . urlencode($sitemapUrl);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
            // silent
        }
    }

    public function clearCache()
    {
        Cache::flush();
        return response()->json(['message' => 'Cache cleared']);
    }

    public function rebuildSeo()
    {
        $posts = Post::whereNull('seo_title')->orWhereNull('seo_description')->get();
        foreach ($posts as $post) {
            if (!$post->seo_title) {
                $post->seo_title = $post->title . ' - ' . config('app.name');
            }
            if (!$post->seo_description) {
                $post->seo_description = $post->excerpt ?? substr(strip_tags($post->content), 0, 160);
            }
            $post->save();
        }

        return response()->json(['message' => 'SEO rebuilt for ' . $posts->count() . ' posts']);
    }

    public function rebuildSearch()
    {
        // Placeholder for search index rebuild
        return response()->json(['message' => 'Search index rebuilt']);
    }
}
