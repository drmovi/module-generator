<?php

namespace Drmovi\LaravelModule\Console\Commands;

use Drmovi\LaravelModule\Services\ModuleGenerator;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ModuleGenerateCommand extends Command
{
    protected $signature = 'module:generate {name : The name of the module (vendor/module-name)}';
    
    protected $description = 'Generate a new module structure';

    protected $files;
    protected $generator;

    public function __construct(Filesystem $files, ModuleGenerator $generator)
    {
        parent::__construct();
        $this->files = $files;
        $this->generator = $generator;
    }

    public function handle()
    {
        $name = $this->argument('name');
        
        if (!str_contains($name, '/')) {
            $this->error('Module name must be in format: vendor/module-name');
            return 1;
        }

        [$vendor, $moduleName] = explode('/', $name, 2);

        $this->info("Generating module: {$name}");

        try {
            $this->generator->generate($vendor, $moduleName);
            $this->generator->updateRootComposer($vendor, $moduleName);
            $this->generator->updatePhpunitXml($vendor, $moduleName);
            
            $this->info("Module {$name} generated successfully!");
            $this->info("Don't forget to run: composer update");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to generate module: {$e->getMessage()}");
            return 1;
        }
    }
}