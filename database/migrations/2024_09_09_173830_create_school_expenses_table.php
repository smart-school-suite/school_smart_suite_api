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
        Schema::create('school_expenses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('expenses_category_id');
            $table->foreign('expenses_category_id')->references('id')->on('school_expenses_category');
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->date('date');
            $table->decimal('amount', 8, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_expenses');
    }
};
