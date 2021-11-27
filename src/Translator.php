<?php

namespace Nanuc\MissingTranslation;

use Facade\FlareClient\Api;
use Facade\FlareClient\Http\Client;
use Facade\FlareClient\Stacktrace\Stacktrace;
use Facade\Ignition\Facades\Flare;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator
{
    public function get($key, array $replace = array(), $locale = null, $fallback = true)
    {
        if(config('missing-translation.enable-realtime-check') && $locale != config('missing-translation.base-locale')) {
            if(!$this->has($key, $locale)) {
                $this->notifyMissingKey($key);
            }
        }

        $result = parent::get($key, $replace, $locale, $fallback);

        return $result;
    }

    // also see https://github.com/laravel/framework/discussions/39798
    public function has($key, $locale = null, $fallback = true)
    {
        $locale = $locale ?: $this->locale;
        $this->load('*', '*', $locale);

        return parent::get($key, [], $locale, false) !== $key || Arr::has($this->loaded['*']['*'][$locale], $key);
    }

    protected function notifyMissingKey($key)
    {
        $report = Flare::createReportFromMessage('Missing translation: ' . $key . ' (' . $this->locale .')', 'TranslationError/' . $this->locale . '/' . Str::of($key)->studly()->limit(20));
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS & ~DEBUG_BACKTRACE_PROVIDE_OBJECT);
        $report->groupByException();

        $backtrace = [$backtrace[0]];
        $backtrace[0]['file'] = $this->locale.':'.$key;

        $stacktrace = new Stacktrace($backtrace, $this->locale.':'.$key, $this->locale.':'.$key, 1);
        $report->stacktrace($stacktrace);

        $api = new Api(new Client(config('flare.key'), ''));
        $api->report($report);
    }
}
