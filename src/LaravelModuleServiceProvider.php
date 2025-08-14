<?php

namespace Drmovi\LaravelModule;

use Drmovi\LaravelModule\Console\Commands\ModuleGenerateCommand;
use Drmovi\LaravelModule\Services\ModuleGenerator;
use Illuminate\Support\ServiceProvider;

class LaravelModuleServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ModuleGenerator::class, function ($app) {
            return new ModuleGenerator($app['files']);
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ModuleGenerateCommand::class,
            ]);
        }
    }
}