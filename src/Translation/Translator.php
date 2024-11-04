<?php

namespace Unusualify\Modularity\Translation;

use Illuminate\Support\Str;
use Illuminate\Translation\Translator as IlluminateTranslator;

class Translator extends IlluminateTranslator
{
    /**
     * The loader implementation.
     *
     * @var Unusualify\Modularity\Support\FileLoader
     */
    protected $loader;

    public function getTranslations()
    {
        $locale = 'tr';
        $group = '*';
        $namespace = '*';
        $lines = $this->loader->load($locale, $group, $namespace);

        $groups = $this->loader->getGroups();

        // dd(
        //     $this,
        //     $this->loader->namespaces(),
        //     $this->loader->jsonPaths(),
        //     $this->loader->getGroups(),
        //     // $this->loader->load($locale, 'validation', '*'),
        //     $this->localeArray($locale),
        //     getLocales(),
        //     get_class_methods($this),
        // );

        $translations = [];
        foreach (getLocales() as $locale) {
            $group = '*';
            $translation = $this->loader->load($locale, '*', '*');

            foreach ($groups as $group) {
                $translation[$group] = $this->loader->load($locale, $group);
            }

            $translations[$locale] = $translation;
        }

        return $translations;
    }

    public function addPath(array|string $path)
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
