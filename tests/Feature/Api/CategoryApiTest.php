<?php

namespace Tests\Feature\Api;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories()
    {
        Category::factory(3)->create();

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_can_show_category()
    {
        $category = Category::factory()->create();

        $response = $this->getJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.name', $category->name);
    }

    public function test_authenticated_user_can_create_category()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/categories', [
            'name' => 'New Category',
            'description' => 'Category description',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Category');
    }

    public function test_authenticated_user_can_update_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/categories/{$category->id}", [
            'name' => 'Updated Category',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Category');
    }

    public function test_authenticated_user_can_delete_category()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/categories/{$category->id}");

        $response->assertStatus(200);
    }
}
