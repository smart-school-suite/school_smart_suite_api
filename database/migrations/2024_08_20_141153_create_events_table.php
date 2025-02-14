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
            $table->date('start_date');
            $table->date('end_date');
            $table->string('location');
            $table->text('description');
            $table->json('attendees')->nullable();
            $table->text('notes')->nullable();
            $table->string('organizer');
            $table->string('status')->nullable();
            $table->string('category');
            $table->string('urgency')->nullable();
            $table->json('tags')->nullable();
            $table->string('duration');
            $table->string('url')->nullable();
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

