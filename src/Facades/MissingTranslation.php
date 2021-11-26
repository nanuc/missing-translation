<?php

namespace Nanuc\MissingTranslation\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Nanuc\MissingTranslation\MissingTranslation
 */
class MissingTranslation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'missing-translation';
    }
}
