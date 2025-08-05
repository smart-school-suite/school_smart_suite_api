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
            $table->enum('status', ['active', 'inactive'])->default('active');
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
            $table->integer('invitee_count')->default(0);
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('event_author', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });

        Schema::create('event_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('school_event_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('value')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        //ev == event
        //inv === invited
        Schema::create('ev_inv_custom_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('ev_inv_preset_groups', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::create('ev_inv_members', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('actorable_type');
            $table->string('actorable_id');
            $table->boolean('is_liked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ev_inv_members');
        Schema::dropIfExists('ev_inv_preset_groups');
        Schema::dropIfExists('ev_inv_custom_groups');
        Schema::dropIfExists('school_event_settings');
        Schema::dropIfExists('event_settings');
        Schema::dropIfExists('event_author');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('event_tags');
        Schema::dropIfExists('event_categories');
    }
};
