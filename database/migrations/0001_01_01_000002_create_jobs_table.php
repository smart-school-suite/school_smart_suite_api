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
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('system_jobs', function (Blueprint $table) {
            $table->string('id')->primary()->index();
            $table->string('type', 150);
            $table->string('context_type');
            $table->string('context_id', 64);
            $table->string('initiated_by_id', 64);
            $table->string('initiated_by_type');
            $table->string('queue', 150);
            $table->enum('status', ['QUEUED', 'PROCESSING', 'COMPLETED', 'PARTIAL_SUCCESS', 'FAILED', 'CANCELLED'])->default('QUEUED');
            $table->string('stage', 150);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->unsignedTinyInteger('max_attempts')->default(3);
            $table->json('payload')->nullable();
            $table->json('out_put')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at');
            $table->timestamps();
        });

        Schema::create('system_job_events', function (Blueprint $table) {
            $table->string('id')->primary()->index();
            $table->enum('status', ['STATUS_CHANGED', 'STAGE_CHANGED', 'PROGRESS_UPDATED', 'RETRY', 'ERROR']);
            $table->text('message')->nullable();
            $table->json('meta')->nullable();
            $table->string('system_job_id');
            $table->foreign('system_job_id')->references('id')->on('system_jobs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
    }
};
