<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // How long a position (election_roles) may be held and re-election rules.
        Schema::create('election_office_terms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('duration_academic_years')->default(1);
            $table->boolean('allow_reelection')->default(false);
            $table->unsignedTinyInteger('maximum_terms')->nullable();
            $table->timestamps();
        });

        // Actual occupants of a position (elected, appointed or replacement).
        Schema::create('election_office_holders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Student / Teacher / Schooladmin. Class names and UUIDs only, so the
            // columns are compact ASCII to keep the index within MySQL's key limit.
            $type = $table->string('holderable_type', 100);
            $id = $table->string('holderable_id', 64);
            if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                $type->charset('ascii')->collation('ascii_bin');
                $id->charset('ascii')->collation('ascii_bin');
            }
            $table->index(['holderable_type', 'holderable_id'], 'election_office_holders_holderable_index');
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
            $table->unsignedInteger('vote_count')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // The school academic years (specialty specific) that make up a tenure.
        Schema::create('election_office_years', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedTinyInteger('term_year_number')->default(1);
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
