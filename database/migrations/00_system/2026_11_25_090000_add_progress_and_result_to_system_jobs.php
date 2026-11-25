<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Imports report progress and results on the system job itself, so the
     * app can read where a job stands without the live event.
     *
     * - progress, result, error_code and error_message: the jobs already
     *   wrote these, but the columns never existed, so the values were lost.
     * - The item counters were TINYINT (max 255), too small for a file with
     *   a few hundred rows. They become INT.
     *
     * Each step checks the column first, so the migration is safe on any
     * database state.
     */
    public function up(): void
    {
        if (!Schema::hasTable('system_jobs')) {
            return;
        }

        Schema::table('system_jobs', function (Blueprint $table) {
            if (!Schema::hasColumn('system_jobs', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0);
            }
            if (!Schema::hasColumn('system_jobs', 'result')) {
                $table->json('result')->nullable();
            }
            if (!Schema::hasColumn('system_jobs', 'error_code')) {
                $table->string('error_code', 100)->nullable();
            }
            if (!Schema::hasColumn('system_jobs', 'error_message')) {
                $table->text('error_message')->nullable();
            }
        });

        $columns = collect(Schema::getColumns('system_jobs'))->keyBy('name');
        $counters = collect(['total_items', 'processed_items', 'successful_items', 'failed_items'])
            ->filter(fn($name) => $columns->has($name)
                && in_array(strtolower($columns[$name]['type_name']), ['tinyint', 'smallint'], true));

        if ($counters->isNotEmpty()) {
            Schema::table('system_jobs', function (Blueprint $table) use ($counters) {
                foreach ($counters as $name) {
                    $table->unsignedInteger($name)->default(0)->change();
                }
            });
        }
    }

    /** Nothing to undo: narrowing the counters again could truncate data. */
    public function down(): void
    {
    }
};
