<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('graduation_batches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->dateTime('graduation_start_date')->nullable();
            $table->dateTime('graduation_end_date')->nullable();
            $table->string('location', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('graduation_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->enum('status', ['pending', 'continuing', 'alumni'])->default('pending');
            $table->enum('eligibility_status', ['pending_review', 'eligible', 'ineligible'])->default('pending_review');
            $table->enum('clearance_status', ['pending', 'cleared', 'blocked'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('graduation_records');
        Schema::dropIfExists('graduation_batches');
    }
};
