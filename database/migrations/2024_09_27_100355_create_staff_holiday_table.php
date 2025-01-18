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
        Schema::create('staff_holiday', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('notes')->nullable();
            $table->text("reason");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_holiday');
    }
};
