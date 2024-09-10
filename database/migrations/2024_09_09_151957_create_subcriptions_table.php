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
        Schema::create('subcriptions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('max_number_students');
            $table->decimal('monthly_price', 8, 2);
            $table->decimal('yearly_price', 8, 2);
            $table->string('description_id');
            $table->foreign('description_id')->references('id')->on('subcription_features');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcriptions');
    }
};
