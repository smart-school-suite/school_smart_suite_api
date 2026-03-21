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
        Schema::create('constraint_types', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('constraint_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 200);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('sem_constraints', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_suggestable')->default(true);
            $table->boolean('is_blockable')->default(true);
            $table->timestamps();
        });

        Schema::create('sem_blockers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 150);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_resolvable')->default(true);
            $table->timestamps();
        });

        Schema::create('sem_blocker_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name', 150);
            $table->string('key', 200);
            $table->text('description');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('constraint_blockers', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('constraint_types');
        Schema::dropIfExists('constraint_categories');
        Schema::dropIfExists('sem_constraints');
        Schema::dropIfExists('sem_blocker');
        Schema::dropIfExists('sem_blocker_categories');
        Schema::dropIfExists('constraint_blockers');
    }
};
