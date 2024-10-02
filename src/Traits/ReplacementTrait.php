<?php

namespace Unusualify\Modularity\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Nwidart\Modules\Support\Stub;

trait ReplacementTrait
{
    use ManageNames;

    protected $name;

    protected $moduleName;

    /**
     * Get the list of files will created.
     *
     * @return array
     */
    public function getFiles()
    {
        return Config::get(unusualBaseKey() . '.stubs.files');
    }

    /**
     * Get the contents of the specified stub file by given stub name.
     *
     *
     * @return string
     */
    protected function getStubContents($stub)
    {
        return (new Stub('/' . $stub . '.stub', $this->getReplacement($stub)))
            ->render();
    }

    /**
     * get the list for the replacements.
     */
    public function getReplacements()
    {
        return Config::get(unusualBaseKey() . '.stubs.replacements');
    }

    /**
     * Get array replacement for the specified stub.
     *
     *
     * @return array
     */
    protected function getReplacement($stub)
    {
        $replacements = $this->getReplacements();

        if (! isset($replacements[$stub])) {
            return [];
        }

        $keys = $replacements[$stub];

        if ($stub === 'json' || $stub === 'composer') {
            if (in_array('PROVIDER_NAMESPACE', $keys, true) === false) {
                $keys[] = 'PROVIDER_NAMESPACE';
            }
        }

        return $this->makeReplaces($keys);

    }

    public function makeReplaces($keys)
    {
        $replaces = [];

        foreach ($keys as $key) {
            if (method_exists($this, $method = 'get' . ucfirst(Str::studly(mb_strtolower($key))) . 'Replacement')) {
                $replaces[$key] = $this->$method();
            } else {
                $replaces[$key] = null;
            }
        }

        return $replaces;
    }

    public function replaceString($string)
    {
        $patterns = [
            '/\$LOWER_NAME\$/' => $this->getLowerNameReplacement(),
            '/\$STUDLY_NAME\$/' => $this->getStudlyNameReplacement(),
            '/\$KEBAB_CASE\$/' => $this->getKebabCase($this->getName()),
            '/\$PASCAL_CASE\$/' => $this->getPascalCase($this->getName()),
            '/\$SNAKE_CASE\$/' => $this->getSnakeCase($this->getName()),
            '/\$CAMEL_CASE\$/' => $this->getCamelCase($this->getName()),
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $string);

    }

    /**
     * Get the route name in lower case.
     *
     * @return string
     */
    protected function getLowerNameReplacement()
    {
        return $this->getLowerName($this->name);
    }

    /**
     * Get the route  name in studly case.
     *
     * @return string
     */
    protected function getStudlyNameReplacement()
    {
        return $this->getStudlyName($this->name);
    }

    /**
     * Get the module name in lower case.
     *
     * @return string
     */
    protected function getLowerModuleNameReplacement()
    {
        return $this->getLowerName($this->moduleName);
    }

    /**
     * Get the module name in lower case.
     *
     * @return string
     */
    protected function getKebabModuleNameReplacement()
    {
        return $this->getKebabCase($this->moduleName);
    }

    /**
     * Get the module name in studly case.
     *
     * @return string
     */
    protected function getStudlyModuleNameReplacement()
    {
        return $this->getStudlyName($this->moduleName);
    }

    /**
     * Get replacement for $VENDOR$.
     *
     * @return string
     */
    protected function getVendorReplacement()
    {
        return unusualConfig('composer.vendor');
    }

    /**
     * Get replacement for $MODULE_NAMESPACE$.
     *
     * @return string
     */
    protected function getModuleNamespaceReplacement()
    {
        return str_replace('\\', '\\\\', config('modules.namespace'));
    }

    /**
     * Get replacement for $AUTHOR$.
     *
     * @return string
     */
    protected function getAuthorReplacement()
    {
        return unusualConfig('composer.author.name');
    }

    /**
     * Get replacement for $AUTHOR_EMAIL$.
     *
     * @return string
     */
    protected function getAuthorEmailReplacement()
    {
        return unusualConfig('composer.author.email');
    }
}
