<?php

namespace Nanuc\MissingTranslation;

use Nanuc\MissingTranslation\Commands\FindMissingTranslationCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MissingTranslationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('missing-translation')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(FindMissingTranslationCommand::class);
    }
}
