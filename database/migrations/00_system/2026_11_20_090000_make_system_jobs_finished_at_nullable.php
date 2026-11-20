<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A system job is created when it starts, so finished_at is empty until it
     * completes or fails. The create migration defines it as nullable, but
     * databases built from an earlier version of that migration have it as
     * NOT NULL without a default, so starting any job (e.g. a semester
     * timetable generation) failed with "Field 'finished_at' doesn't have a
     * default value".
     *
     * category_id is checked too: it is set only for some jobs and comes after
     * finished_at, so it would be the next column to fail on those databases.
     *
     * Only columns that are not nullable yet are changed, so this does
     * nothing on databases that already match the create migration.
     */
    public function up(): void
    {
        if (!Schema::hasTable('system_jobs')) {
            return;
        }

        $columns = collect(Schema::getColumns('system_jobs'))->keyBy('name');

        if (!$columns->has('finished_at')) {
            Schema::table('system_jobs', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable();
            });
        } elseif (!$columns['finished_at']['nullable']) {
            Schema::table('system_jobs', function (Blueprint $table) {
                $table->timestamp('finished_at')->nullable()->change();
            });
        }

        if ($columns->has('category_id') && !$columns['category_id']['nullable']) {
            // MariaDB cannot MODIFY a column that is part of a foreign key.
            $hasForeignKey = collect(Schema::getForeignKeys('system_jobs'))
                ->contains(fn($key) => $key['columns'] === ['category_id']);

            if ($hasForeignKey) {
                Schema::table('system_jobs', fn(Blueprint $table) => $table->dropForeign(['category_id']));
            }

            Schema::table('system_jobs', function (Blueprint $table) {
                $table->uuid('category_id')->nullable()->change();
            });

            if ($hasForeignKey) {
                Schema::table('system_jobs', function (Blueprint $table) {
                    $table->foreign('category_id')->references('id')->on('system_job_categories');
                });
            }
        }
    }

    /**
     * Nothing to undo: the columns stay nullable, as the create migration
     * defines them. Making them NOT NULL again would break starting jobs.
     */
    public function down(): void
    {
    }
};
