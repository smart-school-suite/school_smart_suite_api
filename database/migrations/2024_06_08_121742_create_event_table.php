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
        Schema::create('event_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('event_tags', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('school_events', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('background_image')->nullable();
            $table->string('organizer')->nullable();
            $table->string('location')->nullable();
            $table->integer('likes')->default(0);
            $table->integer('invitee')->default(0);
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->enum('visibility_status', ['visible', 'hidden'])->default('visible');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('audience');
            $table->json('tags');
            $table->timestamps();
        });

        Schema::create('event_authors', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });

        Schema::create('event_like_statuses', function (Blueprint $table) {
             $table->string('id')->primary();
             $table->boolean('status')->default(false);
             $table->string('likeable_id');
             $table->string('likeable_type');
             $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_author');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('event_tags');
        Schema::dropIfExists('event_categories');
    }
};
