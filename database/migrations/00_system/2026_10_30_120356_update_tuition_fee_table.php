<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tuition_fees')) {
            return;
        }

        Schema::table('tuition_fees', function (Blueprint $table) {
            $foreignKeys = Schema::getConnection()
                ->getSchemaBuilder()
                ->getForeignKeys('tuition_fees');

            $hasForeignKey = collect($foreignKeys)->contains(function ($fk) {
                return in_array('level_id', $fk['columns'])
                    && $fk['foreign_table'] === 'levels';
            });

            if ($hasForeignKey) {
                $table->dropForeign(['level_id']);
            }
        });

        if (Schema::hasColumn('tuition_fees', 'level_id')) {
            Schema::table('tuition_fees', function (Blueprint $table) {
                $table->dropColumn('level_id');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
