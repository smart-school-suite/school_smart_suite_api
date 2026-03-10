<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('level');
            $table->string('program_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('level_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('program_name')->unique();
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('levels', function (Blueprint $table) {
            $table->string('level_type_id');
            $table->foreign('level_type_id')->references('id')->on('level_types');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('levels')) {
            Schema::table('levels', function (Blueprint $table) {
                $table->dropForeign(['level_type_id']);
            });
        }

        Schema::dropIfExists('levels');
        Schema::dropIfExists('level_types');
    }
};
