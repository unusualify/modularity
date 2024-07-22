<?php

namespace Unusualify\Modularity\Support;

use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator
{
    /**
     * The loader implementation.
     *
     * @var  Unusualify\Modularity\Support\FileLoader
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
}
