<?php

namespace Tests\Feature\Api;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_pages()
    {
        Page::factory(3)->create();

        $response = $this->getJson('/api/v1/pages');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_authenticated_user_can_create_page()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/pages', [
            'title' => 'About Page',
            'content' => 'About us content',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'About Page');
    }

    public function test_authenticated_user_can_update_page()
    {
        $user = User::factory()->create();
        $page = Page::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/pages/{$page->id}", [
            'title' => 'Updated Page',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.title', 'Updated Page');
    }

    public function test_authenticated_user_can_delete_page()
    {
        $user = User::factory()->create();
        $page = Page::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/v1/pages/{$page->id}");

        $response->assertStatus(200);
    }
}
