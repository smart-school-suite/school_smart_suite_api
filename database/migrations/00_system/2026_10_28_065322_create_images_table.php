<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_logos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_key');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('replaced_at')->nullable();
        });

        Schema::create('student_avatars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_key');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('replaced_at')->nullable();
        });


        Schema::create('school_admin_avatars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_key');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('replaced_at')->nullable();
        });

        Schema::create('teacher_avatars', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_key');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('replaced_at')->nullable();
        });

        Schema::create('school_event_pics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('object_key');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('created_at');
            $table->timestamp('replaced_at')->nullable();
        });

        Schema::create('asset_upload_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('asset_type', 100)->unique();
            $table->unsignedBigInteger('max_file_size_bytes')
                ->nullable();
            $table->json('allowed_mime_types');
            $table->unsignedInteger('max_images_per_owner')
                ->nullable();
            $table->unsignedInteger('max_changes')
                ->nullable();
            $table->unsignedInteger('period_days')
                ->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_admin_avatars');
        Schema::dropIfExists('student_avatars');
        Schema::dropIfExists('teacher_avatars');
        Schema::dropIfExists('school_logos');
        Schema::dropIfExists('school_event_pics');
        Schema::dropIfExists('asset_upload_policies');
    }
};
