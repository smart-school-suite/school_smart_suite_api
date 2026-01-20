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
        Schema::create('activation_codes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('code')->unique();
            $table->boolean('used')->default(false);
            $table->decimal('price', 8, 2);
            $table->integer('duration')->default(365);
            $table->dateTime('expires_at');
            $table->timestamps();
        });

        Schema::create('activation_code_usages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->dateTime('activated_at');
            $table->dateTime('expires_at');
            $table->string('actorable_id')->nullable();
            $table->string('actorable_type')->nullable();
            $table->timestamps();
        });

        Schema::create('activation_code_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->decimal('price', 10, 2);
            $table->enum('status', ['active','inactive']);
            $table->enum('type', ['teacher', 'student']);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activation_code');
        Schema::dropIfExists('activation_code_usages');
        Schema::dropIfExists('activation_code_types');
    }
};
