<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->string('icon');
            $table->json('color');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('tags')->nullable();
            $table->json('audience')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_recipients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('recipient_type');
            $table->uuid('recipient_id');
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('annoucement_authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_audiences');
        Schema::dropIfExists('annoucement_authors');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('announcement_categories');
    }
};
