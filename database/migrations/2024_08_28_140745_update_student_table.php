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
        //
        Schema::table('student', function (Blueprint $table) {
           if(Schema::hasColumn('student', 'level')){
             $table->dropColumn('level');
           }
           $table->string('level_id')->after('phone_number');
           
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
