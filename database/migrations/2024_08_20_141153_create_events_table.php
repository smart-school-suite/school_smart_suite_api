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
        Schema::create('events', function (Blueprint $table) {
            $table->string("id");
            $table->string('title');
            $table->timestamp('start')->useCurrent();
            $table->timestamp('end')->useCurrent();
            $table->string('location');
            $table->text('description');
            $table->json('attendees');
            $table->text('notes')->nullable();
            $table->string('organizer');
            $table->string('status');
            $table->string('category');
            $table->string('urgency');
            $table->json('tags')->nullable();
            $table->string('color');
            $table->string('duration');
            $table->string('url')->nullable();
            $table->string('audience')->nullable();
            $table->string('feedback_link')->nullable();
            $table->json('attachments')->nullable();
            $table->string('background_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};

