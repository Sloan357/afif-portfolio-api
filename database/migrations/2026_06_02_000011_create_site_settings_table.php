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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->default('default')->unique();
            $table->string('site_name');
            $table->boolean('is_active')->default(true);
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('location')->nullable();
            $table->string('primary_domain')->nullable();
            $table->string('frontend_url')->nullable();
            $table->string('admin_url')->nullable();
            $table->foreignId('default_og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('favicon_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->jsonb('social_links')->nullable();
            $table->jsonb('contact_links')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
