<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('voter_status', function (Blueprint $table) {
            $table->string('id');
            $table->string('votable_id');
            $table->string('votable_type');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voter_status');
    }
};
