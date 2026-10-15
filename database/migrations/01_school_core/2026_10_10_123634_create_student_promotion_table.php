<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('status', [
                'pending',
                'evaluating',
                'processing',
                'completed',
                'completed_with_issues',
                'failed',
                'cancelled',
                'scheduled'
            ])->default('pending');
            $table->unsignedInteger('total_students')->default(0);
            $table->unsignedInteger('processed_students')->default(0);
            $table->unsignedInteger('promoted_count')->default(0);
            $table->unsignedInteger('repeated_count')->default(0);
            $table->unsignedInteger('graduated_count')->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedInteger('excluded_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('authorable_type')->nullable();
            $table->uuid('authorable_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('student_promotion_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('outcome', [
                'promoted',
                'repeated',
                'graduated',
                'pending_review',
                'excluded',
            ])->default('pending_review');
            $table->enum('status', [
                'pending',
                'evaluating',
                'completed',
                'failed',
            ])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->string('authorable_type')->nullable();
            $table->uuid('authorable_id')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_runs');
        Schema::dropIfExists('student_promotion_records');
    }
};
