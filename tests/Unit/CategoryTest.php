<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_category()
    {
        $category = Category::factory()->create([
            'name' => 'Technology',
            'slug' => 'technology',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Technology',
            'slug' => 'technology',
        ]);
    }

    public function test_it_has_unique_slug()
    {
        Category::factory()->create(['slug' => 'tech']);
        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::factory()->create(['slug' => 'tech']);
    }

    public function test_it_has_posts()
    {
        $category = Category::factory()->create();
        $user = User::factory()->create();

        Post::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);
        Post::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);

        $this->assertEquals(2, $category->posts()->count());
    }

    public function test_it_can_have_seo_fields()
    {
        $category = Category::factory()->create([
            'seo_title' => 'SEO Title',
            'seo_description' => 'SEO Description',
            'seo_keywords' => 'keyword1, keyword2',
        ]);

        $this->assertEquals('SEO Title', $category->seo_title);
        $this->assertEquals('SEO Description', $category->seo_description);
    }
}
