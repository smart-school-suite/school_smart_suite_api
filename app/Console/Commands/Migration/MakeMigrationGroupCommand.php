<?php

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeMigrationGroupCommand extends Command
{
    //examples
    # Interactive mode (choose group from list)
    // php artisan make:migration create_users_table

    // # Direct mode
    // php artisan make:migration create_users_table --group=01_school_core

    // # With table option
    // php artisan make:migration add_status_to_users --group=01_school_core --table=users

    // # Constraint migration
    // php artisan make:migration add_foreign_keys --group=07_constraint:school_core
    protected $signature = 'make:migration {name : Migration name (e.g., create_users_table)} {--group= : Migration group folder} {--table= : Table name for stub} {--create : Indicates if the migration creates a table}';

    protected $description = 'Create a migration in a specific group folder';

    /**
     * Available migration groups
     */
    protected array $groups = [
        '00_system',
        '01_school_core',
        '02_people',
        '03_academics',
        '04_finance',
        '05_student_ops',
        '06_communication',
        '07_constraints:system',
        '07_constraints:school_core',
        '07_constraints:people',
        '07_constraints:academics',
        '07_constraints:finance',
        '07_constraints:student_ops',
        '07_constraints:communication',
    ];

    public function handle(): int
    {
        $name = $this->argument('name');
        $group = $this->option('group');

        // Interactive group selection if not provided
        if (!$group) {
            $group = $this->choice(
                'Which group should this migration belong to?',
                $this->groups
            );
        }

        // Validate group exists
        if (!in_array($group, $this->groups)) {
            $this->error("Invalid group. Available groups: " . implode(', ', $this->groups));
            return self::FAILURE;
        }

        // Convert group with colon to folder path
        $folderPath = str_replace(':', '/', $group);
        $migrationPath = database_path("migrations/{$folderPath}");

        // Create directory if it doesn't exist
        if (!is_dir($migrationPath)) {
            mkdir($migrationPath, 0755, true);
            $this->line("<info>Created directory:</info> {$folderPath}");
        }

        // Generate migration file
        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_{$name}.php";
        $filepath = "{$migrationPath}/{$filename}";

        // Create stub
        $stub = $this->getMigrationStub();
        $className = Str::studly($name);
        $stub = str_replace(['{{ class }}', '{{ table }}'], [$className, $this->getTableName()], $stub);

        file_put_contents($filepath, $stub);

        $this->info("✓ Migration created: <comment>database/migrations/{$folderPath}/{$filename}</comment>");

        return self::SUCCESS;
    }

    protected function getMigrationStub(): string
    {
        $isCreate = $this->option('create');
        $table = $this->getTableName();

        if ($isCreate) {
            return <<<'STUB'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{{ table }}', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{{ table }}');
    }
};
STUB;
        }

        return <<<'STUB'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
STUB;
    }

    protected function getTableName(): string
    {
        if ($this->option('table')) {
            return $this->option('table');
        }

        $name = $this->argument('name');
        return Str::plural(Str::snake($name));
    }
}
