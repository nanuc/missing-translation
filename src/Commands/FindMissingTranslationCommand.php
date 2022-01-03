<?php

namespace Nanuc\MissingTranslation\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;

class FindMissingTranslationCommand extends Command
{
    public $signature = 'missing-translation:find {to=de : Target language}';

    public $description = 'Find (and correct) missing translations';

    public function handle()
    {
        $path = base_path();

        $stringKeys = [];

        $functions = [
            'trans',
            'trans_choice',
            'Lang::get',
            'Lang::choice',
            'Lang::trans',
            'Lang::transChoice',
            '@lang',
            '@choice',
            '__',
            '$trans.get',
        ];
        $toBeTranslatedMarker = '!E!';

        $groupPattern =                          // See https://regex101.com/r/WEJqdL/6
            "[^\w|>]".                          // Must not have an alphanum or _ or > before real method
            '('.implode('|', $functions).')'.  // Must start with one of the functions
            "\(".                               // Match opening parenthesis
            "[\'\"]".                           // Match " or '
            '('.                                // Start a new group to match:
            '[a-zA-Z0-9_-]+'.               // Must start with group
            "([.](?! )[^\1)]+)+".             // Be followed by one or more items/keys
            ')'.                                // Close group
            "[\'\"]".                           // Closing quote
            "[\),]";                            // Close parentheses or new parameter

        $stringPattern =
            "[^\w]".                                     // Must not have an alphanum before real method
            '('.implode('|', $functions).')'.             // Must start with one of the functions
            "\(".                                          // Match opening parenthesis
            "(?P<quote>['\"])".                            // Match " or ' and store in {quote}
            "(?P<string>(?:\\\k{quote}|(?!\k{quote}).)*)". // Match any string that can be {quote} escaped
            "\k{quote}".                                   // Match " or ' previously matched
            "[\),]";                                       // Close parentheses or new parameter

        if (! File::exists($this->langFile())) {
            File::put($this->langFile(), json_encode([]));
        }

        // Find all PHP + Twig files in the app folder, except for storage
        $finder = new Finder();
        $finder->in($path)->exclude('storage')->exclude('vendor')->name('*.php')->name('*.twig')->name('*.vue')->files();

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            if (preg_match_all("/$stringPattern/siU", $file->getContents(), $matches)) {
                foreach ($matches['string'] as $key) {
                    /*
                    if (preg_match("/(^[a-zA-Z0-9_-]+([.][^\1)\ ]+)+$)/siU", $key, $groupMatches)) {
                        $this->info(str_replace(base_path(), '', $file->getPathname()).': '.$groupMatches[0]);
                        // group{.group}.key format, already in $groupKeys but also matched here
                        // do nothing, it has to be treated as a group
                        continue;
                    }
                    */

                    //TODO: This can probably be done in the regex, but I couldn't do it.
                    //skip keys which contain namespacing characters, unless they also contain a
                    //space, which makes it JSON.
                    if (! (Str::contains($key, '::') && Str::contains($key, '.'))
                        || Str::contains($key, ' ')) {
                        $stringKeys[] = $key;
                    }

                    if (Str::contains($key, $toBeTranslatedMarker)) {
                        $translation = $this->ask('Please enter German translation for "'.str_replace($toBeTranslatedMarker, '', $key).'"');

                        $content = $file->getContents();
                        $content = str_replace($key, $translation, $content);
                        File::put($file->getRealPath(), $content);

                        $translations = $this->getAvailableStrings();
                        $translations[$translation] = str_replace($toBeTranslatedMarker, '', $key);
                        File::put($this->langFile(), json_encode($translations, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));
                    }
                }
            }
        }
        $stringKeys = array_unique($stringKeys);
        $availableStrings = $this->getAvailableStrings();

        $missingTranslations = [];
        foreach ($stringKeys as $stringKey) {
            if (! in_array($stringKey, array_keys($availableStrings))) {
                $missingTranslations[] = $stringKey;
            }
        }

        $this->info('Found ' . count($missingTranslations) . ' missing translations.');

        if ($this->confirm('Do you want to display the missing phrases?')) {
            foreach ($missingTranslations as $missingTranslation) {
                $this->info($missingTranslation);
            }
        }

        if ($this->confirm('Do you want to translate the missing phrases now?')) {
            $useDeepL = $this->confirm('Do you want to use DeepL for auto-translation?');

            foreach ($missingTranslations as $missingTranslation) {
                $proposal = $useDeepL ? $this->autoTranslate($missingTranslation) : null;
                $translation = $this->ask('Please enter translation (locale: ' . $this->argument('to') . ') for "' . $missingTranslation . '"', $proposal);

                if(strlen($translation) > 0) {
                    $translations = $this->getAvailableStrings();
                    $translations[$missingTranslation] = $translation;
                    File::put($this->langFile(), json_encode($translations, JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE + JSON_PRETTY_PRINT));

                }
            }
        }
    }

    public function getAvailableStrings()
    {
        $availableStrings = [];

        $finder = new Finder();
        $finder->in(base_path())
            ->name($this->argument('to').'.json')
            ->files();

        foreach($finder as $file) {
            $availableStrings = array_merge($availableStrings, json_decode(File::get($file->getRealPath()), true));
        }

        return $availableStrings;
    }

    public function langFile()
    {
        return resource_path('lang/'.$this->argument('to').'.json');
    }

    private function autoTranslate($text)
    {
        return Arr::get(Http::get(config('missing-translation.deep-l.endpoint'), [
            'auth_key' => config('missing-translation.deep-l.auth-key'),
            'target_lang' => $this->argument('to'),
            'text' => $text,
        ])->json(), 'translations.0.text');
    }
}
