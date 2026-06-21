<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'posts', 'categories', 'tags', 'pages', 'comments',
        'backlinks', 'backlink_sites', 'external_posts', 'guest_post_orders',
        'settings',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->unsignedBigInteger('domain_id')->nullable()->after('id');
                $t->index('domain_id');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['domain_id']);
                $t->dropColumn('domain_id');
            });
        }
    }
};
