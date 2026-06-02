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
        Schema::create('hero_content_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_content_id')->constrained('hero_contents')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('badge')->nullable();
            $table->string('headline');
            $table->text('description')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->jsonb('capabilities')->nullable();
            $table->jsonb('architecture_items')->nullable();
            $table->timestamps();

            $table->unique(['hero_content_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_content_translations');
    }
};
