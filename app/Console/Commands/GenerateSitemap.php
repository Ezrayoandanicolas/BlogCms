<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate sitemap.xml files';

    public function handle()
    {
        $siteUrl = config('app.url');

        // Main sitemap index
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '  <sitemap><loc>' . $siteUrl . '/sitemap-posts.xml</loc></sitemap>' . "\n";
        $xml .= '  <sitemap><loc>' . $siteUrl . '/sitemap-categories.xml</loc></sitemap>' . "\n";
        $xml .= '  <sitemap><loc>' . $siteUrl . '/sitemap-tags.xml</loc></sitemap>' . "\n";
        $xml .= '</sitemapindex>';
        Storage::disk('public')->put('sitemap.xml', $xml);

        // Posts sitemap
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        Post::where('status', 'published')->each(function ($post) use ($siteUrl, &$xml) {
            $xml .= '  <url>';
            $xml .= '    <loc>' . $siteUrl . '/blog/' . $post->slug . '</loc>';
            $xml .= '    <lastmod>' . $post->updated_at->format('Y-m-d') . '</lastmod>';
            $xml .= '    <priority>0.8</priority>';
            $xml .= '  </url>' . "\n";
        });
        $xml .= '</urlset>';
        Storage::disk('public')->put('sitemap-posts.xml', $xml);

        // Categories sitemap
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        Category::each(function ($cat) use ($siteUrl, &$xml) {
            $xml .= '  <url>';
            $xml .= '    <loc>' . $siteUrl . '/category/' . $cat->slug . '</loc>';
            $xml .= '    <priority>0.5</priority>';
            $xml .= '  </url>' . "\n";
        });
        $xml .= '</urlset>';
        Storage::disk('public')->put('sitemap-categories.xml', $xml);

        // Tags sitemap
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        Tag::each(function ($tag) use ($siteUrl, &$xml) {
            $xml .= '  <url>';
            $xml .= '    <loc>' . $siteUrl . '/tag/' . $tag->slug . '</loc>';
            $xml .= '    <priority>0.3</priority>';
            $xml .= '  </url>' . "\n";
        });
        $xml .= '</urlset>';
        Storage::disk('public')->put('sitemap-tags.xml', $xml);

        $this->info('Sitemap generated successfully!');
    }
}
