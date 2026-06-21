<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_blog_page_returns_200()
    {
        Post::factory(3)->published()->create();

        $response = $this->get('/blog');

        $response->assertStatus(200);
    }

    public function test_single_post_page_returns_200()
    {
        $post = Post::factory()->published()->create();

        $response = $this->get("/blog/{$post->slug}");

        $response->assertStatus(200);
    }

    public function test_category_page_returns_200()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);

        $response = $this->get("/category/{$category->slug}");

        $response->assertStatus(200);
    }

    public function test_robots_txt_returns_200()
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function test_sitemap_returns_200()
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
    }
}
