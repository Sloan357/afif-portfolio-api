<?php

use App\Enums\HeroContentStatus;
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
        Schema::create('hero_contents', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('key')->default('homepage')->unique();
            $table->string('status')->default(HeroContentStatus::Draft->value);
            $table->boolean('is_active')->default(false);
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->foreignId('hero_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedInteger('sort_order')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_contents');
    }
};
