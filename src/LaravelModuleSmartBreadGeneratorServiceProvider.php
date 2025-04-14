<?php

namespace astradevio\LaravelModuleSmartBreadGenerator;

use Illuminate\Support\ServiceProvider;
use astradevio\LaravelModuleSmartBreadGenerator\Console\Commands\LaravelModuleSmartBreadGeneratorCommand;

class LaravelModuleSmartBreadGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/module-smartbread.php', 'module-smartbread');
    }

    public function boot(): void
    {
        $this->configureCommands();
        $this->configurePublishing();
    }

    public function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            LaravelModuleSmartBreadGeneratorCommand::class,
        ]);
    }

    public function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/module-smartbread.php' => config_path('module-smartbread.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'stubs');

    }
}
