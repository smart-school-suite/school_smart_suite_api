<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MakeService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:service {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creating a new service class';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $name = $this->argument('name');
        $path = app_path("Services\{$name}.php");

        if (file_exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        $stub = <<<EOD
        <?php

        namespace App\Services;

        class {$name}
        {
            // Implement your logic here
        }
        EOD;

        file_put_contents($path, $stub);
        $this->info("Service {$name} created successfully.");
    }
}
