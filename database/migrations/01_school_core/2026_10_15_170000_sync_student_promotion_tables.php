<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The create_student_promotion_table migration was edited after it had already run on
     * existing databases. This brings those databases to the same shape; on a fresh database
     * every step is a no-op because the edited create migration already built it.
     */
    public function up(): void
    {
        $this->dropColumnsIfPresent('promotion_runs', ['current_stage', 'initiated_by']);
        $this->addAuthorableIfMissing('promotion_runs');

        Schema::table('promotion_runs', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'evaluating',
                'processing',
                'completed',
                'completed_with_issues',
                'failed',
                'cancelled',
                'scheduled',
            ])->default('pending')->change();
        });

        $this->dropColumnsIfPresent('student_promotion_records', ['reason_code', 'reviewed_by']);
        $this->addAuthorableIfMissing('student_promotion_records');
    }

    public function down(): void
    {
        // Irreversible: the original create migration no longer describes the old shape.
    }

    private function dropColumnsIfPresent(string $tableName, array $columns): void
    {
        $present = array_values(array_filter($columns, fn ($column) => Schema::hasColumn($tableName, $column)));

        if (!empty($present)) {
            Schema::table($tableName, function (Blueprint $table) use ($present) {
                $table->dropColumn($present);
            });
        }
    }

    private function addAuthorableIfMissing(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'authorable_type')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) {
            $table->string('authorable_type')->nullable();
            $table->uuid('authorable_id')->nullable();
        });
    }
};
