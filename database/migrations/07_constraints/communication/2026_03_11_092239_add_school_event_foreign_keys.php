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

    /**
     * Reverse the migrations.
     */
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
    }
};
