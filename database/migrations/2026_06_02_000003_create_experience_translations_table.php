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
        Schema::create('experience_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')->constrained('experiences')->cascadeOnDelete();
            $table->string('locale')->index();
            $table->string('role');
            $table->text('summary')->nullable();
            $table->jsonb('responsibilities')->nullable();
            $table->timestamps();

            $table->unique(['experience_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experience_translations');
    }
};
