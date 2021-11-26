# Missing translations

## Installation

You can install the package via composer:

```bash
composer require nanuc/missing-translation
```

## Usage

### Find and enter missing translations
```
art missing-translation:find {locale}
```

### Report missing translations
**This is basically copied from https://github.com/barryvdh/laravel-translation-manager with the extension that missing translations are reported to Flare.**

Most translations can be found by using the Find command (see above), but in case you have dynamic keys (variables/automatic forms etc), it can be helpful to 'listen' to the missing translations. To detect missing translations, we can swap the Laravel TranslationServiceProvider with a custom provider. In your config/app.php, comment out the original TranslationServiceProvider and add the one from this package:

```
//'Illuminate\Translation\TranslationServiceProvider',
'Barryvdh\TranslationManager\TranslationServiceProvider',
```

This functionality is disabled by default. You can enable it as followed in `.env`:
```
MISSING_TRANSLATION_ENABLE_REALTIME_CHECK=true
```

For this to work correctly please also set the following `.env` parameter to your base language (defaults to `en`).

```
MISSING_TRANSLATION_BASE_LOCALE=de
```
This will extend the Translator and will send a report to Flare, whenever a key is not found, so you have to visit the pages that use them. You shouldn't use this in production, just in development to translate your views, then just switch back.



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
