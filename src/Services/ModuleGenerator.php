<?php

namespace Drmovi\LaravelModule\Services;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleGenerator
{
    protected $files;
    protected $basePath;

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->basePath = base_path();
    }

    public function generate(string $vendor, string $moduleName): void
    {
        $moduleDir = $this->basePath . '/modules/' . $moduleName;
        
        if ($this->files->exists($moduleDir)) {
            throw new \Exception("Module {$moduleName} already exists");
        }

        $this->createDirectoryStructure($moduleDir);
        $this->generateComposerJson($moduleDir, $vendor, $moduleName);
        $this->generateConfigFile($moduleDir, $moduleName);
        $this->generateServiceProvider($moduleDir, $vendor, $moduleName);
        $this->generateSrcStructure($moduleDir, $vendor, $moduleName);
        $this->generateTestsStructure($moduleDir, $vendor, $moduleName);
        $this->generateGitignore($moduleDir);
    }

    protected function createDirectoryStructure(string $moduleDir): void
    {
        $directories = [
            'config',
            'database/migrations',
            'database/factories',
            'database/seeders',
            'src/Console/Commands',
            'src/Http/Controllers',
            'src/Http/Middleware',
            'src/Models',
            'src/Providers',
            'tests/Feature',
            'tests/Unit',
        ];

        foreach ($directories as $dir) {
            $this->files->makeDirectory($moduleDir . '/' . $dir, 0755, true);
        }
    }

    protected function generateComposerJson(string $moduleDir, string $vendor, string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $namespace = ucfirst($vendor) . '\\' . $studlyName;
        
        $composer = [
            'name' => strtolower($vendor) . '/' . $moduleName,
            'description' => "Laravel module: {$moduleName}",
            'type' => 'library',
            'license' => 'MIT',
            'version' => '1.0.0',
            'autoload' => [
                'psr-4' => [
                    $namespace . '\\' => 'src/'
                ]
            ],
            'autoload-dev' => [
                'psr-4' => [
                    $namespace . '\\Tests\\' => 'tests/'
                ]
            ],
            'require' => [
                'php' => '^8.0',
                'illuminate/support' => '^8.0|^9.0|^10.0|^11.0|^12.0'
            ],
            'require-dev' => [
                'phpunit/phpunit' => '^9.0|^10.0'
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        $namespace . '\\Providers\\' . $studlyName . 'ServiceProvider'
                    ]
                ]
            ]
        ];

        $this->files->put(
            $moduleDir . '/composer.json',
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected function generateConfigFile(string $moduleDir, string $moduleName): void
    {
        $config = "<?php\n\nreturn [\n    'enabled' => true,\n];\n";
        $this->files->put($moduleDir . '/config/' . $moduleName . '.php', $config);
    }

    protected function generateServiceProvider(string $moduleDir, string $vendor, string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $namespace = ucfirst($vendor) . '\\' . $studlyName;
        
        $content = "<?php\n\nnamespace {$namespace}\\Providers;\n\nuse Illuminate\\Support\\ServiceProvider;\n\nclass {$studlyName}ServiceProvider extends ServiceProvider\n{\n    public function register()\n    {\n        \$this->mergeConfigFrom(\n            __DIR__.'/../../config/{$moduleName}.php',\n            '{$moduleName}'\n        );\n    }\n\n    public function boot()\n    {\n        \$this->loadRoutesFrom(__DIR__.'/../../routes/web.php');\n        \$this->loadRoutesFrom(__DIR__.'/../../routes/api.php');\n        \$this->loadMigrationsFrom(__DIR__.'/../../database/migrations');\n        \n        \$this->publishes([\n            __DIR__.'/../../config/{$moduleName}.php' => config_path('{$moduleName}.php'),\n        ], 'config');\n    }\n}\n";

        $this->files->put($moduleDir . '/src/Providers/' . $studlyName . 'ServiceProvider.php', $content);
    }

    protected function generateSrcStructure(string $moduleDir, string $vendor, string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $namespace = ucfirst($vendor) . '\\' . $studlyName;

        $controller = "<?php\n\nnamespace {$namespace}\\Http\\Controllers;\n\nuse Illuminate\\Http\\Request;\nuse App\\Http\\Controllers\\Controller;\n\nclass {$studlyName}Controller extends Controller\n{\n    public function index()\n    {\n        return response()->json(['message' => '{$studlyName} module is working!']);\n    }\n}\n";

        $this->files->put($moduleDir . '/src/Http/Controllers/' . $studlyName . 'Controller.php', $controller);

        $model = "<?php\n\nnamespace {$namespace}\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$studlyName} extends Model\n{\n    protected \$fillable = [];\n}\n";

        $this->files->put($moduleDir . '/src/Models/' . $studlyName . '.php', $model);

        $this->files->makeDirectory($moduleDir . '/routes');
        $webRoutes = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse {$namespace}\\Http\\Controllers\\{$studlyName}Controller;\n\nRoute::prefix('{$moduleName}')->group(function () {\n    Route::get('/', [{$studlyName}Controller::class, 'index']);\n});\n";
        
        $this->files->put($moduleDir . '/routes/web.php', $webRoutes);

        $apiRoutes = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\nuse {$namespace}\\Http\\Controllers\\{$studlyName}Controller;\n\nRoute::prefix('api/{$moduleName}')->group(function () {\n    Route::get('/', [{$studlyName}Controller::class, 'index']);\n});\n";
        
        $this->files->put($moduleDir . '/routes/api.php', $apiRoutes);
    }

    protected function generateTestsStructure(string $moduleDir, string $vendor, string $moduleName): void
    {
        $studlyName = Str::studly($moduleName);
        $namespace = ucfirst($vendor) . '\\' . $studlyName;

        $featureTest = "<?php\n\nnamespace {$namespace}\\Tests\\Feature;\n\nuse Illuminate\\Foundation\\Testing\\RefreshDatabase;\nuse Tests\\TestCase;\n\nclass {$studlyName}Test extends TestCase\n{\n    public function test_{$moduleName}_module_works()\n    {\n        \$response = \$this->get('/{$moduleName}');\n        \$response->assertStatus(200);\n    }\n}\n";

        $this->files->put($moduleDir . '/tests/Feature/' . $studlyName . 'Test.php', $featureTest);

        $unitTest = "<?php\n\nnamespace {$namespace}\\Tests\\Unit;\n\nuse PHPUnit\\Framework\\TestCase;\n\nclass {$studlyName}UnitTest extends TestCase\n{\n    public function test_example()\n    {\n        \$this->assertTrue(true);\n    }\n}\n";

        $this->files->put($moduleDir . '/tests/Unit/' . $studlyName . 'UnitTest.php', $unitTest);
    }

    protected function generateGitignore(string $moduleDir): void
    {
        $gitignore = "/vendor/\n/node_modules/\n/.idea/\n/.vscode/\n/.DS_Store\nThumbs.db\n\n# Composer\ncomposer.lock\n\n# PHPUnit\n/.phpunit.cache\n/coverage/\n\n# Environment files\n.env\n.env.local\n.env.*.local\n\n# Log files\n*.log\n\n# Cache\n/.php-cs-fixer.cache\n/.phpstan.cache\n";
        
        $this->files->put($moduleDir . '/.gitignore', $gitignore);
    }

    public function updateRootComposer(string $vendor, string $moduleName): void
    {
        $composerPath = $this->basePath . '/composer.json';
        
        if (!$this->files->exists($composerPath)) {
            throw new \Exception('Root composer.json not found');
        }

        $composer = json_decode($this->files->get($composerPath), true);
        
        if (!isset($composer['repositories'])) {
            $composer['repositories'] = [];
        }

        $moduleRepo = [
            'type' => 'path',
            'url' => './modules/*'
        ];

        $exists = false;
        foreach ($composer['repositories'] as $repo) {
            if (isset($repo['url']) && $repo['url'] === './modules/*') {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $composer['repositories'][] = $moduleRepo;
        }

        if (!isset($composer['require'])) {
            $composer['require'] = [];
        }

        $composer['require'][strtolower($vendor) . '/' . $moduleName] = '^1.0';

        $this->files->put(
            $composerPath,
            json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}