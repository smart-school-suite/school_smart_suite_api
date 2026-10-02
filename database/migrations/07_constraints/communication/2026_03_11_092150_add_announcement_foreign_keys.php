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
        Schema::table('announcement_categories', function (Blueprint $table) {
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->uuid('category_id')->nullable()->index();
            $table->foreign('category_id')->references('id')->on('announcement_categories')->onDelete('set null');
            $table->string('label_id')->index();
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcement_audiences', function (Blueprint $table) {
            $table->uuid('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');

            $table->index(
                ['announcement_id', 'recipient_type', 'recipient_id'],
                'ann_audiences_ann_recipient_idx'
            );

            $table->index(
                ['recipient_type', 'recipient_id'],
                'ann_audiences_recipient_idx'
            );

            $table->index(
                ['announcement_id', 'seen_at'],
                'ann_audiences_ann_seen_idx'
            );
        });

        Schema::table('annoucement_authors', function (Blueprint $table) {
            $table->uuid('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->uuid('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        if (Schema::hasTable('annoucement_authors')) {
            Schema::table('annoucement_authors', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcement_audiences')) {
            Schema::table('announcement_audiences', function (Blueprint $table) {
                $table->dropForeign(['announcement_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropForeign(['label_id']);
                $table->dropForeign(['school_branch_id']);
            });
        }

        if (Schema::hasTable('announcement_categories')) {
            Schema::table('announcement_categories', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
            });
        }
    }
};
