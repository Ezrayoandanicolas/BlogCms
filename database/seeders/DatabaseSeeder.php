<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DomainTheme;
use App\Models\Setting;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin
        User::firstOrCreate(
            ['email' => 'admin@blogcms.test'],
            ['name' => 'Admin', 'password' => Hash::make('password'), 'role' => 'super_admin', 'status' => true]
        );

        // Domain themes
        $domainConfigs = [
            ['domain' => 'localhost:8101', 'slug' => 'default', 'name' => 'BlogCMS', 'desc' => 'Modern Blog CMS built with Laravel'],
            ['domain' => 'localhost:8102', 'slug' => 'magazine', 'name' => 'BlogCMS Magazine', 'desc' => 'Magazine themed Blog CMS'],
            ['domain' => 'localhost:8103', 'slug' => 'dark', 'name' => 'BlogCMS Dark', 'desc' => 'Dark mode Blog CMS'],
        ];
        $mainDomain = null;
        foreach ($domainConfigs as $dc) {
            $d = DomainTheme::where('domain', $dc['domain'])->first();
            if (!$d) {
                $d = DomainTheme::create([
                    'domain' => $dc['domain'],
                    'theme_slug' => $dc['slug'],
                    'settings' => ['site_name' => $dc['name'], 'site_description' => $dc['desc']],
                    'active' => true,
                ]);
            } else {
                $d->update(['theme_slug' => $dc['slug'], 'active' => true]);
            }
            if (!$mainDomain) $mainDomain = $d;
        }

        config(['app.domain_id' => $mainDomain->id]);

        // Default categories
        $categories = ['Technology', 'Lifestyle', 'Travel', 'Business'];
        foreach ($categories as $name) {
            $slug = \Illuminate\Support\Str::slug($name);
            $cat = Category::where('slug', $slug)->first();
            if ($cat) {
                $cat->update(['domain_id' => $mainDomain->id]);
            } else {
                Category::create(['name' => $name, 'slug' => $slug, 'domain_id' => $mainDomain->id]);
            }
        }

        // Themes
        $themeList = [
            ['slug' => 'default', 'name' => 'Default', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Default BlogCMS theme'],
            ['slug' => 'blue', 'name' => 'Blue', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Cyan/blue accent theme'],
            ['slug' => 'bold', 'name' => 'Bold', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Bold typography, full-width imagery, orange accent'],
            ['slug' => 'clean', 'name' => 'Clean', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Minimalist, whitespace, centered, no sidebar'],
            ['slug' => 'dark', 'name' => 'Dark', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Dark mode, purple accents, slate background'],
            ['slug' => 'magazine', 'name' => 'Magazine', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Grid magazine layout, red accent'],
            ['slug' => 'compact', 'name' => 'Compact', 'author' => 'BlogCMS', 'version' => '1.0', 'description' => 'Dense layout, small thumbnails, sky accent'],
        ];
        foreach ($themeList as $t) {
            Theme::firstOrCreate(['slug' => $t['slug']], $t + ['status' => true]);
        }

        // Site settings
        Setting::firstOrCreate(['key' => 'active_theme', 'domain_id' => $mainDomain->id], ['value' => 'default', 'domain_id' => $mainDomain->id]);
        Setting::firstOrCreate(['key' => 'site_name', 'domain_id' => $mainDomain->id], ['value' => 'BlogCMS', 'domain_id' => $mainDomain->id]);
        Setting::firstOrCreate(['key' => 'site_description', 'domain_id' => $mainDomain->id], ['value' => 'Modern Blog CMS built with Laravel', 'domain_id' => $mainDomain->id]);
    }
}
