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
        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('event_category_id')->index();
            $table->foreign('event_category_id')->references('id')->on('event_categories');
        });

        foreach (['event_invitee', 'event_authors', 'event_likes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('event_id')->index();
                $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
                $table->string('school_branch_id')->index();
                $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            });
        }

        Schema::table('event_invitee', function (Blueprint $table) {
            $table->index(['event_id', 'actorable_type', 'actorable_id'], 'event_invitee_event_actor_idx');
            $table->index(['actorable_type', 'actorable_id'], 'event_invitee_actor_idx');
        });

        Schema::table('event_likes', function (Blueprint $table) {
            $table->unique(['event_id', 'likeable_type', 'likeable_id'], 'event_likes_event_likeable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['event_likes', 'event_authors', 'event_invitee'] as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['event_id']);
                    $table->dropForeign(['school_branch_id']);
                });
            }
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
    }
};
