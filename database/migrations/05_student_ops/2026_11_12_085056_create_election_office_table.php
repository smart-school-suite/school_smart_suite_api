<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_office_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('duration_academic_years')->default(1);
            $table->boolean('allow_reelection')->default(false);
            $table->unsignedTinyInteger('maximum_terms')->nullable();
            $table->timestamps();
        });

        Schema::create('election_office_holders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->nullableMorphs('holderable');
            $table->date('started_at');
            $table->date('expected_end_at');
            $table->date('ended_at')->nullable();
            $table->enum('appointment_type', [
                'elected',
                'appointed',
                'replacement',
            ])->default('elected');
            $table->enum('status', [
                'scheduled',
                'active',
                'completed',
                'terminated',
            ])->default('scheduled');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('election_office_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_office_years');
        Schema::dropIfExists('election_office_holders');
        Schema::dropIfExists('election_office_terms');
    }
};
