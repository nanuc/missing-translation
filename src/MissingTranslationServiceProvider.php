<?php

namespace Nanuc\MissingTranslation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Nanuc\MissingTranslation\Commands\MissingTranslationCommand;

class MissingTranslationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('missing-translation')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_missing-translation_table')
            ->hasCommand(MissingTranslationCommand::class);
    }
}
