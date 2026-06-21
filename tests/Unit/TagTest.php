<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_tag()
    {
        $tag = Tag::factory()->create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $this->assertDatabaseHas('tags', [
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);
    }

    public function test_it_has_unique_slug()
    {
        Tag::factory()->create(['slug' => 'php']);
        $this->expectException(\Illuminate\Database\QueryException::class);

        Tag::factory()->create(['slug' => 'php']);
    }

    public function test_it_belongs_to_many_posts()
    {
        $tag = Tag::factory()->create();
        $user = User::factory()->create();

        $post1 = Post::factory()->create(['user_id' => $user->id]);
        $post2 = Post::factory()->create(['user_id' => $user->id]);

        $tag->posts()->attach([$post1->id, $post2->id]);

        $this->assertEquals(2, $tag->posts()->count());
    }
}
