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
        Schema::table('sem_constraints', function (Blueprint $table) {
            $table->string('constraint_category_id', 64);
            $table->foreign('constraint_category_id')->references('id')->on('constraint_categories')->onDelete('cascade');
            $table->string('constraint_type_id', 64);
            $table->foreign('constraint_type_id')->references('id')->on('constraint_types')->onDelete('cascade');
        });

        Schema::table('sem_blockers', function (Blueprint $table) {
            $table->string('sem_blocker_category_id', 64);
            $table->foreign('sem_blocker_category_id')->references('id')->on('sem_blocker_categories')->onDelete('cascade');
        });

        Schema::table('constraint_blockers', function (Blueprint $table) {
            $table->string('constraint_id', 64);
            $table->foreign('constraint_id')->references('id')->on('sem_constraints')->onDelete('cascade');
            $table->string('blocker_id', 64);
            $table->foreign('blocker_id')->references('id')->on('sem_blockers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sem_constraints', function (Blueprint $table) {
            $table->dropForeign(['constraint_type_id']);
            $table->dropColumn('constraint_type_id');
            $table->dropForeign(['constraint_category_id']);
            $table->dropColumn('constraint_category_id');
        });

        Schema::table('sem_blockers', function (Blueprint $table) {
            $table->dropForeign(['sem_blocker_category_id']);
            $table->dropColumn('sem_blocker_category_id');
        });

        Schema::table('constraint_blockers', function (Blueprint $table) {
            $table->dropForeign(['constraint_id']);
            $table->dropColumn('constraint_id');
            $table->dropForeign(['blocker_id']);
            $table->dropColumn('blocker_id');
        });
    }
};
