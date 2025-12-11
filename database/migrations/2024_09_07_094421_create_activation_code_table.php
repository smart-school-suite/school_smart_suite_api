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
            $table->enum('code_type', ['teacher', 'student']);
            $table->enum('status', ['active', 'pending']);
            $table->boolean('used');
            $table->decimal('price', 8, 2);
            $table->integer('duration')->default(365);
            $table->dateTime('expires_at');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

           Schema::create('activation_code_usages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->dateTime('activated_at');
            $table->dateTime('expires_at');
            $table->json('meta')->nullable();
            $table->string('actorable_id')->nullable();
            $table->string('actorable_type')->nullable();
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
    }
};
