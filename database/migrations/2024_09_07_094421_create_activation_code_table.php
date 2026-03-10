<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->enum('status', ['active', 'inactive']);
            $table->enum('type', ['teacher', 'student']);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('activation_codes', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('activation_code_type_id');
            $table->foreign('activation_code_type_id')->references('id')->on('activation_code_types');
        });

        Schema::table('activation_code_usages', function (Blueprint $table) {
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->string('activation_code_id');
            $table->foreign('activation_code_id')->references('id')->on('activation_codes');
        });

        Schema::table('activation_code_types', function (Blueprint $table) {
            $table->string('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('activation_code_usages')) {
            Schema::table('activation_code_usages', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['activation_code_id']);
            });
        }

        if (Schema::hasTable('activation_codes')) {
            Schema::table('activation_codes', function (Blueprint $table) {
                $table->dropForeign(['school_branch_id']);
                $table->dropForeign(['country_id']);
                $table->dropForeign(['activation_code_type_id']);
            });
        }

        if (Schema::hasTable('activation_code_types')) {
            Schema::table('activation_code_types', function (Blueprint $table) {
                $table->dropForeign(['country_id']);
            });
        }

        Schema::dropIfExists('activation_code_usages');
        Schema::dropIfExists('activation_code_types');
        Schema::dropIfExists('activation_codes');
    }
};
