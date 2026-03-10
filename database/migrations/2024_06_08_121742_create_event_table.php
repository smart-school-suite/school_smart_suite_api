<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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

        Schema::create('event_audiences', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('audienceable_id');
            $table->string('audienceable_type');
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

        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('event_audiences', function (Blueprint $table) {
            $table->string('event_id')->index();
            $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('event_category_id')->index();
            $table->foreign('event_category_id')->references('id')->on('event_categories');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('event_audiences')) {
            Schema::table('event_audiences', function (Blueprint $table) {
                $table->dropForeign(['event_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('school_events')) {
            Schema::table('school_events', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['event_category_id']);
            });
        }

        if (Schema::hasTable('event_categories')) {
            Schema::table('event_categories', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }

        Schema::dropIfExists('event_like_statuses');
        Schema::dropIfExists('event_authors');
        Schema::dropIfExists('event_audiences');
        Schema::dropIfExists('school_events');
        Schema::dropIfExists('event_tags');
        Schema::dropIfExists('event_categories');
    }
};
