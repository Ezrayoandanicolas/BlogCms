<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('backlinks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('backlink_sites')->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('set null');
            $table->string('anchor_text')->nullable();
            $table->string('target_url');
            $table->enum('link_type', ['dofollow', 'nofollow', 'ugc', 'sponsored'])->default('dofollow');
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backlinks');
    }
};
