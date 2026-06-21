<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'author',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'author',
        ]);
    }

    public function test_it_has_default_role()
    {
        $user = User::factory()->create();

        $this->assertEquals('author', $user->role);
    }

    public function test_it_can_be_super_admin()
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->assertEquals('super_admin', $user->role);
    }

    public function test_it_has_posts_relationship()
    {
        $user = User::factory()->create();
        $post = \App\Models\Post::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->posts->contains($post));
        $this->assertEquals(1, $user->posts->count());
    }

    public function test_it_is_active_by_default()
    {
        $user = User::factory()->create();

        $this->assertTrue((bool) $user->status);
    }
}
