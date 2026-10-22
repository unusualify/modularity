<?php

namespace Unusualify\Modularous\Translation;

use Illuminate\Support\Str;
use Illuminate\Translation\Translator as IlluminateTranslator;
use Unusualify\Modularous\Support\FileLoader;

class Translator extends IlluminateTranslator
{
    /**
     * The loader implementation.
     *
     * @var FileLoader
     */
    protected $loader;

    public function getTranslations()
    {
        $group = '*';
        $groups = $this->loader->getGroups();

        $translations = [];
        foreach (getLocales() as $locale) {
            $translation = $this->loader->load($locale, '*', '*');

            foreach ($groups as $group) {
                $translation[$group] = $this->loader->load($locale, $group);
            }

            $translations[$locale] = $translation;
        }

        return $translations;
    }

    /**
     * Add a path to the translator.
     *
     * @param string $path
     * @return void
     */
    public function addPath($path)
    {
        $this->loader->addPath($path);
    }

    /**
     * Make the place-holder replacements on a line.
     *
     * @param string $line
     * @return string
     */
    protected function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }

        $shouldReplace = [];

        foreach ($replace as $key => $value) {
            if (is_object($value) && isset($this->stringableHandlers[get_class($value)])) {
                $value = call_user_func($this->stringableHandlers[get_class($value)], $value);
            }

            $shouldReplace[':' . Str::ucfirst($key ?? '')] = Str::ucfirst($value ?? '');
            $shouldReplace[':' . Str::upper($key ?? '')] = Str::upper($value ?? '');
            $shouldReplace[':' . $key] = $value;
            $shouldReplace['{' . Str::ucfirst($key ?? '') . '}'] = Str::ucfirst($value ?? '');
            $shouldReplace['{' . Str::upper($key ?? '') . '}'] = Str::upper($value ?? '');
            $shouldReplace['{' . $key . '}'] = $value;
        }

        return strtr($line, $shouldReplace);
    }
}
