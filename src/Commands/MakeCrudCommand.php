<?php

namespace Tir\Crud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crud:make 
        {name : The name of the module (e.g., Product, Invoice, User)}
        {--model-path= : Path to Models directory (relative to app_path)}
        {--controller-path= : Path to Controllers directory (relative to app_path)}
        {--scaffolder-path= : Path to Scaffolders directory (relative to app_path)}
        {--model-namespace= : Custom Model namespace}
        {--controller-namespace= : Custom Controller namespace}
        {--scaffolder-namespace= : Custom Scaffolder namespace}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Generate a CRUD module with Model, Controller, and Scaffolder (dynamically configurable for any app structure)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        // Validate input
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name)) {
            $this->error('Module name must start with uppercase and contain only alphanumeric characters.');
            return 1;
        }

        // Get configuration with defaults
        $modelPath = $this->option('model-path') ?? 'Models';
        $controllerPath = $this->option('controller-path') ?? 'Http/Controllers';
        $scaffolderPath = $this->option('scaffolder-path') ?? 'Scaffolders';
        
        $modelNamespace = $this->option('model-namespace') ?? 'App\\Models';
        $controllerNamespace = $this->option('controller-namespace') ?? 'App\\Http\\Controllers';
        $scaffolderNamespace = $this->option('scaffolder-namespace') ?? 'App\\Scaffolders';

        $this->line('');
        $this->info('Configuration:');
        $this->line("  Model Path: <fg=cyan>{$modelPath}</>");
        $this->line("  Controller Path: <fg=cyan>{$controllerPath}</>");
        $this->line("  Scaffolder Path: <fg=cyan>{$scaffolderPath}</>");
        $this->line("  Model Namespace: <fg=cyan>{$modelNamespace}</>");
        $this->line("  Controller Namespace: <fg=cyan>{$controllerNamespace}</>");
        $this->line("  Scaffolder Namespace: <fg=cyan>{$scaffolderNamespace}</>");
        $this->line('');

        try {
            $this->generateModel($name, $modelPath, $modelNamespace);
            $this->generateController($name, $controllerPath, $controllerNamespace, $scaffolderNamespace);
            $this->generateScaffolder($name, $scaffolderPath, $scaffolderNamespace, $modelNamespace);

            $this->info("✓ CRUD module '$name' created successfully!");
            $this->newLine();
            $this->showSummary($name, $modelNamespace, $controllerNamespace, $scaffolderNamespace);

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }

    /**
     * Generate the Model class
     */
    protected function generateModel(string $name, string $path, string $namespace): void
    {
        $fullPath = app_path($path);
        $modelPath = $fullPath . DIRECTORY_SEPARATOR . $name . '.php';
        $modelDir = dirname($modelPath);

        // Create directory if it doesn't exist
        if (!File::isDirectory($modelDir)) {
            File::makeDirectory($modelDir, 0755, true);
        }

        // Check if model already exists
        if (File::exists($modelPath)) {
            $this->warn("Model already exists: {$modelPath}");
            return;
        }

        $modelStub = $this->getModelStub($name, $namespace);
        File::put($modelPath, $modelStub);
        $this->line("<fg=green>✓</> Model created: <fg=cyan>{$modelPath}</>");
    }

    /**
     * Generate the Controller class
     */
    protected function generateController(string $name, string $path, string $namespace, string $scaffolderNamespace): void
    {
        $fullPath = app_path($path);
        $controllerPath = $fullPath . DIRECTORY_SEPARATOR . $name . 'Controller.php';
        $controllerDir = dirname($controllerPath);

        // Create directory if it doesn't exist
        if (!File::isDirectory($controllerDir)) {
            File::makeDirectory($controllerDir, 0755, true);
        }

        // Check if controller already exists
        if (File::exists($controllerPath)) {
            $this->warn("Controller already exists: {$controllerPath}");
            return;
        }

        $controllerStub = $this->getControllerStub($name, $namespace, $scaffolderNamespace);
        File::put($controllerPath, $controllerStub);
        $this->line("<fg=green>✓</> Controller created: <fg=cyan>{$controllerPath}</>");
    }

    /**
     * Generate the Scaffolder class
     */
    protected function generateScaffolder(string $name, string $path, string $namespace, string $modelNamespace): void
    {
        $fullPath = app_path($path);
        $scaffolderPath = $fullPath . DIRECTORY_SEPARATOR . $name . 'Scaffolder.php';
        $scaffolderDir = dirname($scaffolderPath);

        // Create directory if it doesn't exist
        if (!File::isDirectory($scaffolderDir)) {
            File::makeDirectory($scaffolderDir, 0755, true);
        }

        // Check if scaffolder already exists
        if (File::exists($scaffolderPath)) {
            $this->warn("Scaffolder already exists: {$scaffolderPath}");
            return;
        }

        $scaffolderStub = $this->getScaffolderStub($name, $namespace, $modelNamespace);
        File::put($scaffolderPath, $scaffolderStub);
        $this->line("<fg=green>✓</> Scaffolder created: <fg=cyan>{$scaffolderPath}</>");
    }

    /**
     * Get the Model stub
     */
    protected function getModelStub(string $name, string $namespace): string
    {
        return <<<PHP
<?php

namespace {$namespace};

use Illuminate\Database\Eloquent\Model;

class {$name} extends Model
{
    protected \$fillable = [];

    protected \$casts = [];
}
PHP;
    }

    /**
     * Get the Controller stub
     */
    protected function getControllerStub(string $name, string $namespace, string $scaffolderNamespace): string
    {
        $scaffolderClass = "{$scaffolderNamespace}\\{$name}Scaffolder";

        return <<<PHP
<?php

namespace {$namespace};

use {$scaffolderClass};
use Tir\Crud\Controllers\CrudController;

class {$name}Controller extends CrudController
{
    protected function setScaffolder(): string
    {
        return {$name}Scaffolder::class;
    }
}
PHP;
    }

    /**
     * Get the Scaffolder stub
     */
    protected function getScaffolderStub(string $name, string $namespace, string $modelNamespace): string
    {
        $modelClass = "{$modelNamespace}\\{$name}";
        $moduleName = Str::snake($name);
        $moduleTitle = "modules." . $moduleName;

        return <<<PHP
<?php

namespace {$namespace};

use {$modelClass};
use Tir\Crud\Support\Scaffold\BaseScaffolder;
use Tir\Crud\Support\Scaffold\Fields\Text;
use Tir\Crud\Support\Enums\ActionType;
use Tir\Crud\Support\Scaffold\Actions;

class {$name}Scaffolder extends BaseScaffolder
{
    protected function setModel(): string
    {
        return {$name}::class;
    }

    protected function setModuleName(): string
    {
        return '{$moduleName}';
    }

    protected function setModuleTitle(): string
    {
        return trans('{$moduleTitle}', '{$name}');
    }

    protected function setActions(): array
    {
        return Actions::all();
    }

    protected function setFields(): array
    {
        return [
            // Add your fields here
            // Text::make('name')->required(),
            // Text::make('description'),
        ];
    }

    protected function setTableFields(): array
    {
        return [
            // Define which fields appear in the table
            // Text::make('name'),
            // Text::make('description'),
        ];
    }
}
PHP;
    }

    /**
     * Show a summary of generated files
     */
    protected function showSummary(string $name, string $modelNamespace, string $controllerNamespace, string $scaffolderNamespace): void
    {
        $this->info('Generated classes:');
        $this->line("  • <fg=cyan>Model</> - {$modelNamespace}\\{$name}");
        $this->line("  • <fg=cyan>Controller</> - {$controllerNamespace}\\{$name}Controller");
        $this->line("  • <fg=cyan>Scaffolder</> - {$scaffolderNamespace}\\{$name}Scaffolder");
        $this->newLine();
        $this->info('Next steps:');
        $this->line('  1. Update the Model class with your database schema');
        $this->line('  2. Add fields to the Scaffolder class');
        $this->line('  3. Define table fields in setTableFields()');
        $this->line('  4. Register routes in your routes file');
        $this->line('  5. Add translations for the module');
        $this->newLine();
    }
}
