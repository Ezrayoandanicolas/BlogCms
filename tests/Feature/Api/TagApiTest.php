<?php

namespace Tests\Feature\Api;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_tags()
    {
        Tag::factory(3)->create();

        $response = $this->getJson('/api/v1/tags');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_authenticated_user_can_create_tag()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tags', [
            'name' => 'New Tag',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Tag');
    }

    public function test_authenticated_user_can_delete_tag()
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/tags/{$tag->id}");

        $response->assertStatus(200);
    }
}
