<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A draft event only needs one detail filled in, so the title and category
     * may be empty until the event is published. Publishing still requires them.
     *
     * MariaDB cannot MODIFY a column that is part of a foreign key, so the FK
     * must be dropped first and re-added afterward.
     */
    public function up(): void
    {
        if (!Schema::hasTable('school_events')) {
            return;
        }

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('title', 150)->nullable()->change();
        });

        if (Schema::hasColumn('school_events', 'event_category_id')) {
            Schema::table('school_events', function (Blueprint $table) {
                $table->dropForeign(['event_category_id']);
            });

            Schema::table('school_events', function (Blueprint $table) {
                $table->uuid('event_category_id')->nullable()->change();
            });

            Schema::table('school_events', function (Blueprint $table) {
                $table->foreign('event_category_id')
                    ->references('id')
                    ->on('event_categories');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_events')) {
            return;
        }

        if (Schema::hasColumn('school_events', 'event_category_id')) {
            Schema::table('school_events', function (Blueprint $table) {
                $table->dropForeign(['event_category_id']);
            });

            Schema::table('school_events', function (Blueprint $table) {
                $table->uuid('event_category_id')->nullable(false)->change();
            });

            Schema::table('school_events', function (Blueprint $table) {
                $table->foreign('event_category_id')
                    ->references('id')
                    ->on('event_categories');
            });
        }

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('title', 150)->nullable(false)->change();
        });
    }
};
