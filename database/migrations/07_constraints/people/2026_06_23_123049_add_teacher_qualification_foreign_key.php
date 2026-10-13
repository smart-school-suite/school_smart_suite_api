<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The qualifications table is created after the teacher constraints migration,
     * so this foreign key lives here. Databases migrated before it was moved may
     * already have it, hence the existence check.
     */
    public function up(): void
    {
        if ($this->hasQualificationForeignKey()) {
            return;
        }

        Schema::table('teacher_qualifications', function (Blueprint $table) {
            $table->foreign('qualification_id')->references('id')->on('qualifications');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!$this->hasQualificationForeignKey()) {
            return;
        }

        Schema::table('teacher_qualifications', function (Blueprint $table) {
            $table->dropForeign(['qualification_id']);
        });
    }

    private function hasQualificationForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('teacher_qualifications'))
            ->contains(fn ($foreignKey) => $foreignKey['columns'] === ['qualification_id']);
    }
};
