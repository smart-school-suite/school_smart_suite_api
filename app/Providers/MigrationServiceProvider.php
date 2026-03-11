<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MigrationServiceProvider extends ServiceProvider
{
    /**
     * Domain groups — order is the contract.
     * Tables must exist before constraints reference them.
     */
    protected array $groups = [
        // Foundation — no dependencies
        '00_system',

        // Core entities
        '01_school_core',
        '02_people',

        // Academic & financial structure
        '03_academics',
        '04_finance',
        '05_student_ops',
        '06_communication',

        // Constraints mirror domain order
        '07_constraints/system',
        '07_constraints/school_core',
        '07_constraints/people',
        '07_constraints/academics',
        '07_constraints/finance',
        '07_constraints/student_ops',
        '07_constraints/communication',
    ];

    public function boot(): void
    {
        foreach ($this->groups as $group) {
            $this->loadMigrationsFrom(
                database_path("migrations/{$group}")
            );
        }
    }
}
