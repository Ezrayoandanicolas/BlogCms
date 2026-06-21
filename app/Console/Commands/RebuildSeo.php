<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Console\Command;

class RebuildSeo extends Command
{
    protected $signature = 'seo:rebuild';
    protected $description = 'Rebuild SEO metadata for all content';

    public function handle()
    {
        Post::whereNull('seo_title')->each(function ($post) {
            $post->update([
                'seo_title' => $post->seo_title ?: $post->title,
                'seo_description' => $post->seo_description ?: $post->excerpt,
            ]);
        });

        Category::whereNull('seo_title')->each(function ($cat) {
            $cat->update([
                'seo_title' => $cat->seo_title ?: $cat->name,
                'seo_description' => $cat->seo_description ?: $cat->description,
            ]);
        });

        $this->info('SEO metadata rebuilt successfully!');
    }
}
