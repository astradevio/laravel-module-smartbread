<?php

namespace astradevio\LaravelModuleSmartBread;

use Illuminate\Support\ServiceProvider;
use astradevio\LaravelModuleSmartBread\Console\Commands\SmartBreadGeneratorCommand;
use astradevio\LaravelModuleSmartBread\Console\Commands\SmartBreadReplacerCommand;

class SmartBreadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/smartbread.php', 'smartbread');
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
            SmartBreadGeneratorCommand::class,
            SmartBreadReplacerCommand::class,
        ]);
    }

    public function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/smartbread.php' => config_path('smartbread.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../stubs' => base_path('stubs'),
        ], 'stubs');

    }
}
