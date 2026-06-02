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
        Schema::create('ai_request_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('ai_draft_id')->nullable()->constrained('ai_drafts')->nullOnDelete();
            $table->nullableMorphs('requestable');
            $table->string('task_type');
            $table->string('status');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('prompt_version')->nullable();
            $table->string('locale')->nullable();
            $table->string('source_locale')->nullable()->default('en');
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->unsignedInteger('total_tokens')->nullable();
            $table->unsignedInteger('cost_minor_units')->nullable();
            $table->string('currency')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->string('request_hash')->nullable();
            $table->text('input_summary')->nullable();
            $table->text('output_summary')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'task_type']);
            $table->index('provider');
            $table->index('model');
            $table->index('locale');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_request_logs');
    }
};
