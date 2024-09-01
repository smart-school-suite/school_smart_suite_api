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
        Schema::table('specialty', function (Blueprint $table) {
            // Drop the existing level column if it exists
            // You might want to do this only if you're sure there's no data loss
            if (Schema::hasColumn('sspecialty', 'level')) {
                $table->dropColumn('level');
            }
            
            // Add level_id column
            $table->string('level_id')->after('school_fee'); // Adjust 'some_column' to where you want to place it

            // Adding the foreign key constraint
            $table->foreign('level_id')->references('id')->on('education_levels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
