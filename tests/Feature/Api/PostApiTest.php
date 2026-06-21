<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_published_posts()
    {
        Post::factory(3)->published()->create();

        $response = $this->getJson('/api/v1/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['total', 'per_page', 'current_page', 'last_page'],
            ]);
    }

    public function test_can_list_all_posts_as_admin()
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        Post::factory(3)->create();

        $response = $this->actingAs($user)->getJson('/api/v1/posts');

        $response->assertStatus(200);
    }

    public function test_can_show_post_by_slug()
    {
        $post = Post::factory()->published()->create();

        $response = $this->getJson("/api/v1/posts/{$post->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.title', $post->title);
    }

    public function test_authenticated_user_can_create_post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/posts', [
            'title' => 'New Test Post',
            'content' => 'Content of the test post',
            'status' => 'published',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'New Test Post');
    }

    public function test_unauthenticated_user_cannot_create_post()
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'Unauthorized Post',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_get_popular_posts()
    {
        Post::factory(5)->published()->create(['views' => 100]);

        $response = $this->getJson('/api/v1/posts/popular');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_can_search_posts()
    {
        Post::factory()->published()->create(['title' => 'Unique Searchable Title']);
        Post::factory(3)->published()->create();

        $response = $this->getJson('/api/v1/posts/search?q=Unique');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_can_filter_by_category()
    {
        $category = Category::factory()->create();
        Post::factory(2)->published()->create(['category_id' => $category->id]);
        Post::factory(3)->published()->create();

        $response = $this->getJson("/api/v1/posts/category/{$category->slug}");

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('meta.total'));
    }

    public function test_authenticated_user_can_update_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated Title',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Title');
    }

    public function test_authenticated_user_can_delete_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Post deleted']);
    }

    public function test_can_sync_external_post()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/posts/sync', [
            'source' => 'external-blog',
            'external_id' => 'ext-001',
            'title' => 'Synced Post',
            'content' => 'Content from external source',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Synced Post');
    }

    public function test_can_bulk_store_posts()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/posts/bulk', [
            'posts' => [
                ['title' => 'Bulk Post 1', 'content' => 'Content 1'],
                ['title' => 'Bulk Post 2', 'content' => 'Content 2'],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertCount(2, $response->json('data'));
    }
}
