<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_result_publications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_result_publications');
    }
};
