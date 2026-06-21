<?php

namespace Tests\Unit;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_page()
    {
        $page = Page::factory()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
        ]);

        $this->assertDatabaseHas('pages', [
            'title' => 'About Us',
            'slug' => 'about-us',
        ]);
    }

    public function test_it_has_unique_slug()
    {
        Page::factory()->create(['slug' => 'about']);
        $this->expectException(\Illuminate\Database\QueryException::class);

        Page::factory()->create(['slug' => 'about']);
    }

    public function test_it_can_have_seo_fields()
    {
        $page = Page::factory()->create([
            'seo_title' => 'About SEO Title',
            'seo_description' => 'About SEO Description',
        ]);

        $this->assertEquals('About SEO Title', $page->seo_title);
        $this->assertEquals('About SEO Description', $page->seo_description);
    }
}
