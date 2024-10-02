<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

if (! function_exists('unusualTrans')) {
    function unusualTrans($key, $replace = [])
    {
        $locale = unusualConfig('locale', unusualConfig('fallback_locale', 'en'));

        return trans($key, $replace, $locale);
    }
}

if (! function_exists('___')) {
    function ___($key = null, $replace = [], $locale = null)
    {
        // Default behavior
        if (is_null($key)) {
            return $key;
        }

        // dd(
        //     $key,
        //     \Lang::has($key),
        //     trans()->has($key),
        //     trans('auth'),
        //     trans('authentication'),
        //     __($key),
        //     trans()->get('*', [], config('app.fallback_locale')),
        // );

        // dd(
        //     app('translator')
        // );
        // Search in .json file
        $search = Arr::get(trans()->get('*'), $key);

        if ($search !== null) {
            return trans_replacements($search, $replace);
        }

        if (trans()->has($key)) {
            return trans($key, $replace, $locale);
        }

        // Return .json fallback
        $fallback = Arr::get(trans()->get('*', [], config('app.fallback_locale')), $key);
        if ($fallback !== null) {
            return trans_replacements($fallback, $replace);
        }
        // Return key name if not found
        else {
            return $key;
        }
    }
}
if (! function_exists('hasUnusualTrans')) {
    /**
     * Determine if a translation exists.
     *
     * @param string $key
     * @param string|null $locale
     * @param bool $fallback
     * @return bool
     */
    function hasUnusualTrans($key, $locale = null, $fallback = true)
    {
        $locale = $locale ?: App::getLocale();

        $line = ___($key, [], $locale, $fallback);

        dd(
            $key,
            $line
        );
        // For JSON translations, the loaded files will contain the correct line.
        // Otherwise, we must assume we are handling typical translation file
        // and check if the returned line is not the same as the given key.
        // if (! is_null($this->loaded['*']['*'][$locale][$key] ?? null)) {
        //     return true;
        // }

        return $line !== $key;
    }
}
if (! function_exists('trans_replacements')) {
    function trans_replacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        $shouldReplace = [];
        foreach ($replace as $key => $value) {
            $shouldReplace[':' . Str::ucfirst($key)] = Str::ucfirst($value);
            $shouldReplace[':' . Str::upper($key)] = Str::upper($value);
            $shouldReplace[':' . $key] = $value;
            $shouldReplace['{' . Str::ucfirst($key) . '}'] = Str::ucfirst($value);
            $shouldReplace['{' . Str::upper($key) . '}'] = Str::upper($value);
            $shouldReplace['{' . $key . '}'] = $value;
        }

        return strtr($line, $shouldReplace);
    }
}

// function trans_choice($key, $number, array $replace = [], $locale = null)
// {
//     // Get message
//     $message = __($key, $replace, $locale);

//     // If the given "number" is actually an array or countable we will simply count the
//     // number of elements in an instance.
//     if (is_array($number) || $number instanceof Countable)
//     $number = count($number);

//     $replace['count'] = $number;

//     return trans_replacements(
//         trans()->getSelector()->choose($message, $number, $locale = App::getLocale()),
//         $replace
//     );
// }

if (! function_exists('getLabelFromLocale')) {
    /**
     * @param string $code
     * @return string
     */
    function getLabelFromLocale($code, $native = false)
    {
        $base_key = unusualBaseKey();

        if (class_exists(Locale::class)) {
            if ($native) {
                return Locale::getDisplayName($code, $code);
            }

            return Locale::getDisplayName($code, config("{$base_key}.locale", config("{$base_key}.fallback_locale", 'en')));
        }

        $languageTexts = getCode2LanguageTexts();

        if (isset($languageTexts[$code])) {
            $lang = $languageTexts[$code];
            if (is_array($lang) && isset($lang[1]) && $native) {
                return $lang[1];
            }

            if (is_array($lang) && isset($lang[0])) {
                return $lang[0];
            }

            return $lang;
        }

        return $code;
    }
}

if (! function_exists('getCode2LanguageTexts')) {
    function getCode2LanguageTexts()
    {
        return [
            'ab' => 'Abkhazian',
            'aa' => 'Afar',
            'af' => 'Afrikaans',
            'sq' => 'Albanian',
            'am' => 'Amharic',
            'ar' => ['Arabic', 'العربية'],
            'an' => 'Aragonese',
            'hy' => 'Armenian',
            'as' => 'Assamese',
            'ay' => 'Aymara',
            'az' => 'Azerbaijani',
            'ba' => 'Bashkir',
            'eu' => 'Basque',
            'bn' => 'Bengali (Bangla)',
            'dz' => 'Bhutani',
            'bh' => 'Bihari',
            'bi' => 'Bislama',
            'br' => 'Breton',
            'bg' => ['Bulgarian', 'български език'],
            'my' => 'Burmese',
            'be' => 'Byelorussian (Belarusian)',
            'km' => 'Cambodian',
            'ca' => 'Catalan',
            'zh' => 'Chinese',
            'zh-Hans' => ['Chinese (simplified)', '简体中文'],
            'zh-Hant' => ['Chinese (traditional)', '繁體中文'],
            'co' => 'Corsican',
            'hr' => 'Croatian',
            'cs' => ['Czech', 'čeština'],
            'da' => ['Danish', 'Dansk'],
            'nl' => ['Dutch', 'Nederlands'],
            'en' => 'English',
            'eo' => 'Esperanto',
            'et' => 'Estonian',
            'fo' => 'Faeroese',
            'fa' => 'Farsi',
            'fj' => ['Fiji', 'Suomi'],
            'fi' => 'Finnish',
            'fr' => ['French', 'Français'],
            'fy' => 'Frisian',
            'gd' => 'Gaelic (Scottish)',
            'gv' => 'Gaelic (Manx)',
            'gl' => 'Galician',
            'ka' => 'Georgian',
            'de' => ['German', 'Deutsch'],
            'el' => ['Greek', 'Ελληνικά'],
            'kl' => 'Kalaallisut (Greenlandic)',
            'gn' => 'Guarani',
            'gu' => 'Gujarati',
            'ht' => 'Haitian Creole',
            'ha' => 'Hausa',
            'he' => 'Hebrew',
            'iw' => 'Hebrew',
            'hi' => 'Hindi',
            'hu' => ['Hungarian', 'Magyar'],
            'is' => 'Icelandic',
            'io' => 'Ido',
            'id' => 'Indonesian',
            'in' => 'Indonesian',
            'ia' => 'Interlingua',
            'ie' => 'Interlingue',
            'iu' => 'Inuktitut',
            'ik' => 'Inupiak',
            'ga' => 'Irish',
            'it' => ['Italian', 'Italiano'],
            'ja' => ['Japanese', '日本語'],
            'jv' => 'Javanese',
            'kn' => 'Kannada',
            'ks' => 'Kashmiri',
            'kk' => 'Kazakh',
            'rw' => 'Kinyarwanda (Ruanda)',
            'ky' => 'Kirghiz',
            'rn' => 'Kirundi (Rundi)',
            'ko' => ['Korean', '한국어'],
            'ku' => 'Kurdish',
            'lo' => 'Laothian',
            'la' => 'Latin',
            'lv' => 'Latvian (Lettish)',
            'li' => 'Limburgish ( Limburger)',
            'ln' => 'Lingala',
            'lt' => 'Lithuanian',
            'mk' => 'Macedonian',
            'mg' => 'Malagasy',
            'ms' => 'Malay',
            'ml' => 'Malayalam',
            'mt' => 'Maltese',
            'mi' => 'Maori',
            'mr' => 'Marathi',
            'mo' => 'Moldavian',
            'mn' => 'Mongolian',
            'na' => 'Nauru',
            'ne' => 'Nepali',
            'no' => 'Norwegian',
            'oc' => 'Occitan',
            'or' => 'Oriya',
            'om' => 'Oromo (Afan, Galla)',
            'ps' => 'Pashto (Pushto)',
            'pl' => ['Polish', 'Polski'],
            'pt' => ['Portuguese', 'Português'],
            'pa' => 'Punjabi',
            'qu' => 'Quechua',
            'rm' => 'Rhaeto-Romance',
            'ro' => ['Romanian', 'Română'],
            'ru' => ['Russian', 'Русский'],
            'sm' => 'Samoan',
            'sg' => 'Sango',
            'sa' => 'Sanskrit',
            'sr' => 'Serbian',
            'sh' => 'Serbo-Croatian',
            'st' => 'Sesotho',
            'tn' => 'Setswana',
            'sn' => 'Shona',
            'ii' => 'Sichuan Yi',
            'sd' => 'Sindhi',
            'si' => 'Sinhalese',
            'ss' => 'Siswati',
            'sk' => 'Slovak',
            'sl' => 'Slovenian',
            'so' => 'Somali',
            'es' => ['Spanish', 'Español'],
            'su' => 'Sundanese',
            'sw' => 'Swahili (Kiswahili)',
            'sv' => ['Swedish', 'Svenska'],
            'tl' => 'Tagalog',
            'tg' => 'Tajik',
            'ta' => 'Tamil',
            'tt' => 'Tatar',
            'te' => 'Telugu',
            'th' => ['Thai', 'ไทย'],
            'bo' => 'Tibetan',
            'ti' => 'Tigrinya',
            'to' => 'Tonga',
            'ts' => 'Tsonga',
            'tr' => ['Turkish', 'Türkçe'],
            'tk' => 'Turkmen',
            'tw' => 'Twi',
            'ug' => 'Uighur',
            'uk' => ['Ukrainian', 'Українська'],
            'ur' => 'Urdu',
            'uz' => 'Uzbek',
            'vi' => 'Vietnamese',
            'vo' => 'Volapük',
            'wa' => 'Wallon',
            'cy' => 'Welsh',
            'wo' => 'Wolof',
            'xh' => 'Xhosa',
            'yi' => 'Yiddish',
            'ji' => 'Yiddish',
            'yo' => 'Yoruba',
            'zu' => 'Zulu',
        ];
    }
}

if (! function_exists('getLanguagesForVueStore')) {
    /**
     * @param array $form_fields
     * @param bool $translate
     * @return array
     */
    function getLanguagesForVueStore($form_fields = [], $translate = true)
    {
        $manageMultipleLanguages = count(getLocales()) > 1;
        if ($manageMultipleLanguages && $translate) {
            $allLanguages = Collection::make(getLocales())->map(function ($locale, $index) use ($form_fields) {
                return [
                    'shortlabel' => mb_strtoupper($locale),
                    'label' => getLabelFromLocale($locale),
                    'value' => $locale,
                    'disabled' => false,
                    'published' => $form_fields['translations']['active'][$locale] ?? ($index === 0),
                ];
            });

            return [
                'all' => $allLanguages,
                'active' => request()->has('lang') ? $allLanguages->where('value', request('lang'))->first() : null,
            ];
        }

        $locale = config('app.locale');

        return [
            'all' => [
                [
                    'shortlabel' => mb_strtoupper($locale),
                    'label' => getLabelFromLocale($locale),
                    'value' => $locale,
                    'disabled' => false,
                    'published' => true,
                ],
            ],
        ];
    }
}
