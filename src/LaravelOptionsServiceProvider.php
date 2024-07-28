<?php

namespace blumewas\LaravelOptions;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelOptionsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-options')
            ->hasConfigFile()
            ->hasMigration('create_options_table');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function packageBooted()
    {
        $this->app->singleton(OptionsService::class);
    }
}
