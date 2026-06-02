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
        Schema::create('lab_project_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_project_id')->constrained('lab_projects')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->text('problem')->nullable();
            $table->text('approach')->nullable();
            $table->longText('architecture_notes')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->jsonb('seo_keywords')->nullable();
            $table->timestamps();

            $table->unique(['lab_project_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_project_translations');
    }
};
