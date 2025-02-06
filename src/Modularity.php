<?php

namespace Unusualify\Modularity;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Json;
use Unusualify\Modularity\Exceptions\ModularitySystemPathException;

class Modularity extends FileRepository
{
    /**
     * @var ConfigRepository
     */
    private $modularityConfig;

    /**
     * @var CacheManager
     */
    private $modularityCache;

    /**
     * @var string
     */
    private static $authGuardName = 'modularity';

    /**
     * @var string
     */
    private static $authProviderName = 'modularity_users';

    /**
     * @var string
     */
    private static $translationCacheKey = 'modularity-languages';

    /**
     * @var string
     */
    private $appPath = null;

    /**
     * @var string
     */
    private $vendorPath = null;

    /**
     * @var string
     */
    private $vendorDir = null;

    /**
     * @var string
     */
    private $retainModulesPath = null;

    /**
     * The constructor.
     *
     * @param string|null $path
     */
    public function __construct(Container $app, $path = null)
    {
        parent::__construct($app, $path);

        $this->appPath = realpath(get_installed_composer()['root']['install_path']);
        $this->vendorPath = realpath(get_installed_composer()['versions']['unusualify/modularity']['install_path']);
        $this->vendorDir = trim(Str::replaceFirst($this->appPath, '', $this->vendorPath), DIRECTORY_SEPARATOR);

        $this->modularityCache = $app['cache'];
        $this->modularityConfig = $app['config'];

        $this->retainModulesPath = $this->modularityConfig->get('modules.paths.modules');
    }

    /**
     * {@inheritdoc}
     */
    protected function createModule(...$args)
    {
        return new \Unusualify\Modularity\Module($args[1], $args[2] ?? null);
    }

    /**
     * Get all modules.
     */
    public function all(): array
    {
        if (! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->formatCached($this->getCached());
    }

    /**
     * Format the cached data as array of modules.
     *
     * @param array $cached
     * @return array
     */
    protected function formatCached($cached)
    {
        $modules = [];

        $resetCache = false;
        $basePath = base_path();
        $pathPattern = preg_quote("{$basePath}", '/');

        foreach ($cached as $name => $module) {
            $path = $module['path'];

            if (! preg_match("/{$pathPattern}/", $path)) {
                $resetCache = true;

                break;
            }
            $modules[$name] = $this->createModule($this->app, $name, $path);
        }

        if ($resetCache) {
            return $this->scan();
        }

        return $modules;
    }

    /**
     * Get & scan all modules.
     *
     * @return array
     */
    public function scan()
    {
        $paths = $this->getScanPaths();

        $modules = [];

        foreach ($paths as $key => $path) {
            // dd(
            //     $paths,
            //     $path,
            //     $this->getFiles()->glob(
            //         "{$path}/module.json"
            //     )
            // );
            $manifests = $this->getFiles()->glob("{$path}/module.json");

            is_array($manifests) || $manifests = [];
            foreach ($manifests as $manifest) {
                $name = Json::make($manifest)->get('name');
                // if(preg_match('/oguzhanbukcuoglu/', $manifest)){
                //     dd(
                //         $manifest,
                //         dirname($manifest),
                //         $name,
                //         $manifests,
                //     );
                // }
                $modules[$name] = $this->createModule($this->app, $name, dirname($manifest));

                // dd($path, $manifests, $paths,  Json::make($manifest), $modules);

            }
        }

        return $modules;
    }

    /**
     * Get scanned modules paths.
     */
    public function getScanPaths(): array
    {
        $paths = $this->paths;

        $paths[] = $this->getPath();

        if ($this->config('scan.enabled')) {
            $paths = array_merge($this->config('scan.paths'), $paths);
        }

        $paths = array_map(function ($path) {
            return Str::endsWith($path, '/*') ? $path : Str::finish($path, '/*');
        }, $paths);

        return $paths;
    }

    /**
     * Get the authentication guard name used by Modularity
     *
     * @return string The configured auth guard name
     */
    public static function getAuthGuardName()
    {
        return self::$authGuardName;
    }

    /**
     * Get the authentication provider name used by Modularity
     *
     * @return string The configured auth provider name
     */
    public static function getAuthProviderName()
    {
        return self::$authProviderName;
    }

    /**
     * Get cached modules.
     *
     * @return array
     */
    public function getCached()
    {
        $store = $this->modularityCache->store($this->modularityConfig->get('modules.cache.driver'));

        if ($store->has($this->config('cache.key'))) {
            return $store->get($this->config('cache.key'));
        } else {
            $store->set($this->config('cache.key'), $this->toCollection()->toArray(), $this->config('cache.lifetime'));

            return $this->toCollection()->toArray();
        }
    }

    /**
     * Clear the modules cache if it is enabled
     */
    public function clearCache()
    {
        app('cache')->forget($this->config('cache.key'));
    }

    /**
     * Disable the modules cache
     */
    public function disableCache()
    {
        return config([
            'modules.cache.enabled' => false,
        ]);
    }

    final public function isDevelopment()
    {
        return get_installed_composer()['root']['name'] === 'unusualify/modularity-dev';
    }

    final public function isProduction()
    {
        return ! $this->isDevelopment();
    }

    public function setSystemModulesPath()
    {
        if($this->isProduction()){
            throw new ModularitySystemPathException;
        }

        config([
            'modules.paths.modules' => $this->getVendorPath('modules'),
        ]);
    }

    public function revertSystemModulesPath()
    {
        config([
            'modules.paths.modules' => $this->retainModulesPath,
        ]);
    }

    public function getAppUrl()
    {
        return $this->config('app_url');
    }

    public function getAdminAppUrl()
    {
        return $this->config('admin_app_url');
    }

    /**
     * {@inheritDoc}
     */
    public function config(string $key, $default = null)
    {
        return $this->modularityConfig->get('modules.' . $key,
            $this->modularityConfig->get('modularity.' . $key, $default)
        );
    }

    public function getAdminRouteNamePrefix()
    {
        return rtrim(ltrim($this->config('admin_route_name_prefix', 'admin'), '.'), '.');
    }

    public function getAdminUrlPrefix()
    {
        return $this->config('admin_app_url')
            ? false
            : rtrim(ltrim($this->config('admin_app_path', 'admin'), '/'), '/');
    }

    public function getSystemUrlPrefix()
    {
        return $this->config('system_prefix', 'system-settings');
    }

    public function getSystemRouteNamePrefix()
    {
        return snakeCase(studlyName($this->getSystemUrlPrefix()));
    }

    public function getTranslations()
    {
        $cache_key = static::$translationCacheKey;

        $cache = Cache::store('file');

        if ($cache->has($cache_key) && false) {
            return $cache->get($cache_key);
        }

        $translations = app('translator')->getTranslations();

        $cache->set($cache_key, json_encode($translations), 600);

        return $translations;
    }

    public function clearTranslations()
    {
        $cache_key = static::$translationCacheKey;

        Cache::forget($cache_key);
    }

    public function getGroupedModules($group_name)
    {
        return array_filter($this->allEnabled(), function ($item) use ($group_name) {
            $module_config = $item->getConfig();

            return isset($module_config['group']) && $module_config['group'] === $group_name;
        });
    }

    public function getSystemModules()
    {
        return $this->getGroupedModules('system');
    }

    public function getModules()
    {
        return array_filter($this->allEnabled(), function ($item) {
            $module_config = $item->getConfig();

            return ! isset($module_config['group']) || ! $module_config['group'];
        });
    }

    public function deleteModule(string $name): bool
    {
        $module = null;

        $this->scan();

        foreach ($this->all() as $moduleInstance) {
            if ($moduleInstance->getStudlyName() === studlyName($name)) {
                $module = $moduleInstance;

                break;
            }
        }

        if ($module) {
            $res = $module->delete();

            if ($res) {
                $this->clearCache();

                return $res;
            }

            return $res;
        }

        return false;
    }

    public function getModels($routeName)
    {
        $models = [];

        foreach ($this->allEnabled() as $key => $module) {
            $entityPath = $module->getDirectoryPath('Entities');
            if (! file_exists($entityPath)) {
                continue;
            }

            foreach ($this->getClasses($entityPath) as $_class) {
                if (get_class_short_name(App::make($_class)) === studlyName($routeName)) {
                    $models[] = $_class;
                }
            }
        }

        return $models;
    }

    public function getClasses($path)
    {
        $classes = [];

        foreach (ClassMapGenerator::createMap($path) as $class => $file) {
            $classes[] = $class;
        }

        return $classes;
    }

    /**
     * Get vendor path.
     *
     * @param string $dir
     * @return string
     */
    final public function getVendorPath($dir = '')
    {
        if (! $dir) {
            return $this->vendorPath;
        }

        return concatenate_path($this->vendorPath, $dir);
        // return realpath(concatenate_path($this->vendorPath, $dir));
    }

    /**
     * Get vendor path.
     *
     * @param string $dir
     * @return string
     */
    final public function getVendorDir($dir = '')
    {
        if (! $dir) {
            return $this->vendorDir;
        }

        return concatenate_path($this->vendorDir, $dir);
    }

    /**
     * Get modularity namespace.
     *
     * @param \Nwidart\Modules\Module $module
     * @return string
     */
    public function getVendorNamespace($append = null)
    {
        return concatenate_namespace(modularityConfig('namespace'), $append);
    }
}
