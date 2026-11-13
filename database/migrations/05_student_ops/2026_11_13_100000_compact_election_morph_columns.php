<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Compacts the election morph columns.
 *
 * They only hold class names (e.g. App\Models\Schooladmin) and UUIDs, but were
 * created as utf8mb4 varchar(255) pairs. Combined with UUID columns, that made
 * the election unique keys up to ~2.5 KB, close to MySQL's 3072-byte key limit
 * and over it on servers with a 767/1000-byte limit. As ASCII varchar(100)/(64)
 * the largest key is ~600 bytes.
 *
 * A new migration (rather than editing the ones that already ran) so deployed
 * and fresh databases end up with the same structure.
 */
return new class extends Migration
{
    private const TYPE_LENGTH = 100;
    private const ID_LENGTH = 64;

    /** table => [morph name, nullable] */
    private const MORPHS = [
        'election_participants' => ['participantable', true],
        'election_applications' => ['applicantable', true],
        'election_candidates' => ['candidateable', true],
        'election_votes' => ['votable', false],
        'election_office_holders' => ['holderable', false],
    ];

    public function up(): void
    {
        foreach (self::MORPHS as $table => [$name, $nullable]) {
            $this->alter($table, $name, $nullable, self::TYPE_LENGTH, self::ID_LENGTH, 'ascii', 'ascii_bin');
        }
    }

    public function down(): void
    {
        foreach (self::MORPHS as $table => [$name, $nullable]) {
            $this->alter($table, $name, $nullable, 255, 255, 'utf8mb4', 'utf8mb4_unicode_ci');
        }
    }

    private function alter(string $table, string $name, bool $nullable, int $typeLength, int $idLength, string $charset, string $collation): void
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, "{$name}_type") || !Schema::hasColumn($table, "{$name}_id")) {
            return;
        }

        // Character sets only exist on MySQL/MariaDB.
        $withCharset = in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);

        Schema::table($table, function (Blueprint $blueprint) use ($name, $nullable, $typeLength, $idLength, $charset, $collation, $withCharset) {
            foreach (["{$name}_type" => $typeLength, "{$name}_id" => $idLength] as $column => $length) {
                $definition = $blueprint->string($column, $length)->nullable($nullable);
                if ($withCharset) {
                    $definition->charset($charset)->collation($collation);
                }
                $definition->change();
            }
        });
    }
};
