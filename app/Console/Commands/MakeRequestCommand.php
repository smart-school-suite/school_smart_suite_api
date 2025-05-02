<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:requests {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create FormRequest classes for Create, Update, and BulkUpdate operations.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');
        // Ensure the name starts with a capital letter
        $baseName = Str::studly($name);

        // Determine the directory
        $dir = 'app/Http/Requests';
        if (strpos($baseName, '/')) {
            $parts = explode('/', $baseName);
            $baseName = array_pop($parts); // Get the last part
            $dir .= '/' . implode('/', $parts); // Recreate the directory
        }
        // Create directory if it doesn't exist
        if (!File::isDirectory(base_path($dir))) {
            File::makeDirectory(base_path($dir), 0755, true); //recursive
        }

        $this->createRequest($dir, 'Create' . $baseName . 'Request');
        $this->createUpdateRequest($dir, 'Update' . $baseName . 'Request');
        $this->createBulkUpdateRequest($dir, 'BulkUpdate' . $baseName . 'Request');

        $this->info("Requests [Create{$baseName}Request, Update{$baseName}Request, BulkUpdate{$baseName}Request] created successfully in {$dir}!");
        return 0;
    }

    /**
     * Create a standard request file.
     *
     * @param string $dir
     * @param string $name
     * @return void
     */
    private function createRequest(string $dir, string $name): void
    {
        $path = base_path("{$dir}/{$name}.php");
        if (File::exists($path)) {
            $this->warn("{$name} already exists.");
            return;
        }

        $content = $this->generateRequestContent($name);
        File::put($path, $content);
    }

    /**
     * Create an update request file.
     *
     * @param string $dir
     * @param string $name
     * @return void
     */
    private function createUpdateRequest(string $dir, string $name): void
    {
        $path = base_path("{$dir}/{$name}.php");
        if (File::exists($path)) {
            $this->warn("{$name} already exists.");
            return;
        }
        $content = $this->generateUpdateRequestContent($name);
        File::put($path, $content);
    }

    /**
     * Create a bulk update request file.
     *
     * @param string $dir
     * @param string $name
     * @return void
     */
    private function createBulkUpdateRequest(string $dir, string $name): void
    {
        $path = base_path("{$dir}/{$name}.php");
        if (File::exists($path)) {
            $this->warn("{$name} already exists.");
            return;
        }

        $content = $this->generateBulkUpdateRequestContent($name);
        File::put($path, $content);
    }

    /**
     * Generate content for a standard request.
     *
     * @param string $name
     * @return string
     */
    private function generateRequestContent(string $name): string
    {
        return <<<EOT
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$name} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Define your validation rules here
            // 'field_name' => 'required|string|max:255',
        ];
    }
}

EOT;
    }

    /**
     * Generate content for an update request.
     *
     * @param string $name
     * @return string
     */
    private function generateUpdateRequestContent(string $name): string
    {
        return <<<EOT
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$name} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Define your update validation rules here
            // 'id' => 'required|integer|exists:your_table,id',
            // 'field_name' => 'sometimes|string|max:255',
        ];
    }
}
EOT;
    }

    /**
     * Generate content for a bulk update request.
     *
     * @param string $name
     * @return string
     */
    private function generateBulkUpdateRequestContent(string $name): string
    {
        return <<<EOT
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$name} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Update this as needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // Define your bulk update validation rules here
            'data' => 'required|array',
            'data.*.id' => 'required|integer|exists:your_table,id',
            // 'data.*.field_name' => 'sometimes|string|max:255',
        ];
    }
}
EOT;
    }
}
