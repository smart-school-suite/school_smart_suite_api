<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make school_admins.gender_id nullable.
     *
     * Each step checks the current state first: MySQL cannot roll back schema changes, so a
     * failed earlier attempt may already have dropped the foreign key. gender_id stays a uuid
     * because genders.id is a string key, not an integer.
     */
    public function up(): void
    {
        if ($this->hasGenderForeignKey()) {
            Schema::table('school_admins', function (Blueprint $table) {
                $table->dropForeign(['gender_id']);
            });
        }

        Schema::table('school_admins', function (Blueprint $table) {
            $table->uuid('gender_id')->nullable()->change();
        });

        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreign('gender_id')
                ->references('id')
                ->on('genders')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if ($this->hasGenderForeignKey()) {
            Schema::table('school_admins', function (Blueprint $table) {
                $table->dropForeign(['gender_id']);
            });
        }

        Schema::table('school_admins', function (Blueprint $table) {
            $table->uuid('gender_id')->nullable(false)->change();
        });

        Schema::table('school_admins', function (Blueprint $table) {
            $table->foreign('gender_id')->references('id')->on('genders');
        });
    }

    private function hasGenderForeignKey(): bool
    {
        return collect(Schema::getForeignKeys('school_admins'))
            ->contains(fn ($foreignKey) => $foreignKey['columns'] === ['gender_id']);
    }
};
