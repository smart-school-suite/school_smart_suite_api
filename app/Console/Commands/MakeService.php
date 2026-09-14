<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeService extends Command
{
    protected $signature = 'make:service {name}';
    protected $description = 'Create a new service class';

    public function handle(): int
    {
        $name = $this->argument('name');
        $className = class_basename($name);
        $namespace = trim(str_replace('/', '\\', dirname(str_replace('\\', '/', $name))), '\\');
        $fullNamespace = 'App\\Services' . ($namespace ? "\\{$namespace}" : '');
        $path = app_path('Services/' . str_replace('\\', '/', $name) . '.php');

        if (File::exists($path)) {
            $this->error("Service {$name} already exists!");
            return self::FAILURE;
        }

        File::ensureDirectoryExists(dirname($path));

        File::put($path, $this->buildStub($fullNamespace, $className));

        $this->info("Service {$name} created successfully.");
        return self::SUCCESS;
    }

    protected function buildStub(string $namespace, string $className): string
    {
        return <<<PHP
        <?php

        namespace {$namespace};

        class {$className}
        {
            //
        }
        PHP;
    }
}
