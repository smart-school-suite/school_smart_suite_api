<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('event_audiences') && ! Schema::hasTable('event_invitee')) {
            Schema::rename('event_audiences', 'event_invitee');
        }

        if (Schema::hasTable('event_invitee')) {
            Schema::table('event_invitee', function (Blueprint $table) {
                if (Schema::hasColumn('event_invitee', 'audienceable_id')) {
                    $table->renameColumn('audienceable_id', 'actorable_id');
                }
                if (Schema::hasColumn('event_invitee', 'audienceable_type')) {
                    $table->renameColumn('audienceable_type', 'actorable_type');
                }
            });
        }

        if (Schema::hasTable('event_authors')) {
            Schema::table('event_authors', function (Blueprint $table) {
                if (Schema::hasColumn('event_authors', 'authorable_id')) {
                    $table->renameColumn('authorable_id', 'actorable_id');
                }
                if (Schema::hasColumn('event_authors', 'authorable_type')) {
                    $table->renameColumn('authorable_type', 'actorable_type');
                }
            });
        }

        if (Schema::hasTable('event_like_statuses') && ! Schema::hasTable('event_likes')) {
            Schema::rename('event_like_statuses', 'event_likes');
        }

        if (Schema::hasTable('event_likes') && Schema::hasColumn('event_likes', 'status')) {
            Schema::table('event_likes', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (Schema::hasTable('school_events')) {
            $columnsToAdd = [];

            if (! Schema::hasColumn('school_events', 'audience')) {
                $columnsToAdd[] = 'audience';
            }
            if (! Schema::hasColumn('school_events', 'tags')) {
                $columnsToAdd[] = 'tags';
            }

            if (! empty($columnsToAdd)) {
                Schema::table('school_events', function (Blueprint $table) use ($columnsToAdd) {
                    if (in_array('audience', $columnsToAdd, true)) {
                        $table->json('audience')->nullable();
                    }
                    if (in_array('tags', $columnsToAdd, true)) {
                        $table->json('tags')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('school_events')) {
            Schema::table('school_events', function (Blueprint $table) {
                if (Schema::hasColumn('school_events', 'audience')) {
                    $table->dropColumn('audience');
                }
                if (Schema::hasColumn('school_events', 'tags')) {
                    $table->dropColumn('tags');
                }
            });
        }

        if (Schema::hasTable('event_authors')) {
            Schema::table('event_authors', function (Blueprint $table) {
                if (Schema::hasColumn('event_authors', 'actorable_id')) {
                    $table->renameColumn('actorable_id', 'authorable_id');
                }
                if (Schema::hasColumn('event_authors', 'actorable_type')) {
                    $table->renameColumn('actorable_type', 'authorable_type');
                }
            });
        }

        if (Schema::hasTable('event_likes') && ! Schema::hasColumn('event_likes', 'status')) {
            Schema::table('event_likes', function (Blueprint $table) {
                $table->boolean('status')->default(false);
            });
        }

        if (Schema::hasTable('event_likes') && ! Schema::hasTable('event_like_statuses')) {
            Schema::rename('event_likes', 'event_like_statuses');
        }

        if (Schema::hasTable('event_invitee')) {
            Schema::table('event_invitee', function (Blueprint $table) {
                if (Schema::hasColumn('event_invitee', 'actorable_id')) {
                    $table->renameColumn('actorable_id', 'audienceable_id');
                }
                if (Schema::hasColumn('event_invitee', 'actorable_type')) {
                    $table->renameColumn('actorable_type', 'audienceable_type');
                }
            });

            if (! Schema::hasTable('event_audiences')) {
                Schema::rename('event_invitee', 'event_audiences');
            }
        }
    }
};
