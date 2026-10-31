<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('event_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('event_invitee', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('actorable_id');
            $table->string('actorable_type');
            $table->timestamps();
        });

        Schema::create('school_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('organizer')->nullable();
            $table->string('location')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('audience')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });

        Schema::create('event_authors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });

        Schema::create('event_likes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->boolean('status')->default(false);
            $table->string('likeable_id');
            $table->string('likeable_type');
            $table->timestamps();
        });

    }

    public function down(): void
    {

        Schema::dropIfExists('event_likes');
        Schema::dropIfExists('event_authors');
        Schema::dropIfExists('event_invitee');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('event_tags');
        Schema::dropIfExists('event_categories');
    }
};
