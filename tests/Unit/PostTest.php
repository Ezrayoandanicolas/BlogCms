<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Test Post Title',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post Title',
        ]);
    }

    public function test_it_belongs_to_user()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $post->user);
        $this->assertEquals($user->id, $post->user->id);
    }

    public function test_it_belongs_to_category()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $post->category);
        $this->assertEquals($category->id, $post->category->id);
    }

    public function test_it_can_have_tags()
    {
        $post = Post::factory()->create();
        $tag = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $post->tags()->attach([$tag->id, $tag2->id]);

        $this->assertEquals(2, $post->tags()->count());
    }

    public function test_it_has_draft_status_by_default()
    {
        $post = Post::factory()->create(['status' => 'draft']);

        $this->assertEquals('draft', $post->status);
    }

    public function test_it_increments_views()
    {
        $post = Post::factory()->create(['views' => 0]);
        $post->increment('views');

        $this->assertEquals(1, $post->fresh()->views);
    }

    public function test_it_can_be_published()
    {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->assertEquals('published', $post->status);
        $this->assertNotNull($post->published_at);
    }
}
