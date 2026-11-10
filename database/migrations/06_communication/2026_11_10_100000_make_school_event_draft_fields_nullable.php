<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A draft event only needs one detail filled in, so the title and category
     * may be empty until the event is published. Publishing still requires them.
     */
    public function up(): void
    {
        if (!Schema::hasTable('school_events')) {
            return;
        }

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('title', 150)->nullable()->change();
            if (Schema::hasColumn('school_events', 'event_category_id')) {
                $table->uuid('event_category_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('school_events')) {
            return;
        }

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('title', 150)->nullable(false)->change();
            if (Schema::hasColumn('school_events', 'event_category_id')) {
                $table->uuid('event_category_id')->nullable(false)->change();
            }
        });
    }
};
