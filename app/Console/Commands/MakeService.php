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
        $name = $this->argument('name');
        $path = app_path("Services/" . str_replace('\\', '/', $name) . ".php");

        // Create the directory structure if it doesn't exist
        $directoryPath = dirname($path);
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }

        if (file_exists($path)) {
            $this->error("Service {$name} already exists!");
            return;
        }

        // Stub for the Service class
        $stub = <<<EOD
<?php

namespace App\Services\\{$this->getNamespace($name)};

class {$this->getClassName($name)}
{
    // Implement your logic here
}
EOD;

        file_put_contents($path, $stub);
        $this->info("Service {$name} created successfully.");
    }

    /**
     * Get the class name from the given name.
     *
     * @param string $name
     * @return string
     */
    protected function getClassName($name)
    {
        $segments = explode('\\', $name);
        return array_pop($segments); // Get the last segment as class name
    }

    /**
     * Get the namespace from the given name.
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        $segments = explode('\\', $name);
        array_pop($segments); // Remove the class name segment
        return implode('\\', $segments); // Join remaining segments for namespace
    }
}
