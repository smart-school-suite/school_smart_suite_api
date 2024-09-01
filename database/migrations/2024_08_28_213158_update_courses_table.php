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
        Schema::table('courses', function(Blueprint $table){
            if(Schema::hasColumn('courses', 'level')){
                $table->dropColumn('level');
              }
              $table->string('level_id')->after('credit');
              
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
