<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A branch can share another branch's subscription. The sharing branch keeps
 * its own subscription row, pointing at the one it shares, so every check
 * that reads a branch's subscription keeps working.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->string('shared_from_id')->nullable()->index();
            $table->foreign('shared_from_id')->references('id')->on('school_subscriptions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['shared_from_id']);
            $table->dropColumn('shared_from_id');
        });
    }
};
