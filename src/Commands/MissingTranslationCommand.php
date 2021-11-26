<?php

namespace Nanuc\MissingTranslation\Commands;

use Illuminate\Console\Command;

class MissingTranslationCommand extends Command
{
    public $signature = 'missing-translation';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
