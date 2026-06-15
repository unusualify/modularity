<?php

namespace Unusualify\Modularous;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\Cms\Entities\Concerns\HasParentSegment;
use Nwidart\Modules\FileRepository;
use Nwidart\Modules\Json;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;
use Unusualify\Modularous\Exceptions\ModularousSystemPathException;

class Modularous extends FileRepository
{
    /**
     * @var ActivatorInterface
     */
    private $activator;

    /**
     * @var string
     */
    private static $authGuardName = 'modularous';

    /**
     * @var string
     */
    private static $authProviderName = 'modularous_users';

    /**
     * @var string
     */
    private static $translationCacheKey = 'modularous-languages';

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
     * The callback that should be used to create the page title.
     *
     * @var \Closure|null
     */
    public static $pageTitleCallback;

    /**
     * The callback that should be used to disable language based prices.
     *
     * @var \Closure|null
     */
    public static $disableLanguageBasedPricesCallback;

    /**
     * The constructor.
     *
     * @param string|null $path
     */
    public function __construct(Container $app, $path = null)
    {
        parent::__construct($app, $path);

        $this->appPath = realpath(get_installed_composer()['root']['install_path']);
        $versions = get_installed_composer()['versions'];

        if (isset($versions['unusualify/modularous'])) {
            $this->vendorPath = realpath($versions['unusualify/modularous']['install_path']);
        } elseif (isset($versions['unusualify/modularous'])) {
            $this->vendorPath = realpath($versions['unusualify/modularous']['install_path']);
        } else {
            throw new \Exception('Modularous or Modularous not found in composer.json');
        }
        $this->vendorDir = trim(Str::replaceFirst($this->appPath, '', $this->vendorPath), DIRECTORY_SEPARATOR);
        $this->activator = $app[\Nwidart\Modules\Contracts\ActivatorInterface::class];

        $this->retainModulesPath = $this->app['config']->get('modules.paths.modules');

    }

    /**
     * Get the authentication guard name used by Modularous
     *
     * @return string The configured auth guard name
     */
    public static function getAuthGuardName()
    {
        return self::$authGuardName;
    }

    /**
     * Get the authentication provider name used by Modularous
     *
     * @return string The configured auth provider name
     */
    public static function getAuthProviderName()
    {
        return self::$authProviderName;
    }

    /**
     * {@inheritdoc}
     */
    protected function createModule(Container $app, string $name, string $path): Module
    {
        return new Module($app, $name, $path);
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
     * Get & scan all modules.
     */
    public function scan(): array
    {
        $paths = $this->getScanPaths();

        $modules = [];

        foreach ($paths as $key => $path) {
            $manifests = $this->getFiles()->glob("{$path}/module.json");

            is_array($manifests) || $manifests = [];
            foreach ($manifests as $manifest) {
                $name = Json::make($manifest)->get('name');

                $modules[mb_strtolower($name)] = $this->createModule($this->app, $name, dirname($manifest));
            }
        }

        return $modules;
    }

    /**
     * Get cached modules.
     *
     * @return array
     */
    public function getCached()
    {
        $store = $this->app['cache']->store($this->app['config']->get('modules.cache.driver'));

        if ($store->has($this->config('cache.key'))) {
            return $store->get($this->config('cache.key'));
        } else {
            $store->set($this->config('cache.key'), $this->toCollection()->toArray(), $this->config('cache.lifetime'));

            return $this->toCollection()->toArray();
        }
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
     * Clear the modules cache if it is enabled
     */
    public function clearCache()
    {
        app('cache')->forget($this->config('cache.key'));

        if (method_exists($this->activator, 'flushCache')) {
            $this->activator->flushCache(); // for modules_statuses.json cache
        }
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

    /**
     * Get all modules.
     */
    public function all(): array
    {
        if ($this->app->runningInConsole() || ! $this->config('cache.enabled')) {
            return $this->scan();
        }

        return $this->formatCached($this->getCached());
    }

    /**
     * Get modules by status.
     */
    public function getByStatus($status): array
    {
        $modules = [];

        /** @var Module $module */
        foreach ($this->all() as $name => $module) {
            if ($this->activator->hasStatus($module, $status)) {
                $modules[mb_strtolower($name)] = $module;
            }
        }

        return $modules;
    }

    /**
     * Check if a module exists.
     */
    public function hasModule(string $moduleName): bool
    {
        return $this->has($moduleName);
    }

    /**
     * {@inheritDoc}
     */
    public function config(string $key, $default = null)
    {
        return $this->app['config']->get('modules.' . $key,
            $this->app['config']->get('modularous.' . $key, $default)
        );
    }

    final public function isDevelopment()
    {
        return get_installed_composer()['root']['name'] === 'unusualify/modularous-dev'
            || get_installed_composer()['root']['name'] === 'unusualify/modularous-dev';
    }

    final public function isProduction()
    {
        return ! $this->isDevelopment();
    }

    final public function shouldUseCollationForSearch()
    {
        return $this->config('use_collation_for_search', false);
    }

    /**
     * Check if inertia should be used.
     *
     * @return bool
     */
    final public function shouldUseInertia()
    {
        return $this->config('use_inertia', false);
    }

    /**
     * Check if transaction fee should be included.
     *
     * @return bool
     */
    final public function shouldIncludeTransactionFee()
    {
        return $this->config('include_transaction_fee', false);
    }

    /**
     * Check if country based VAT rates should be used.
     *
     * @return bool
     */
    final public function shouldUseCountryBasedVatRates()
    {
        return $this->config('use_country_based_vat_rates', false);
    }

    /**
     * Create a page title callback.
     *
     * @param \Closure $callback
     * @return void
     */
    public static function createPageTitle($callback)
    {
        self::$pageTitleCallback = $callback;
    }

    /**
     * Get the page title.
     *
     * @return string
     */
    final public function pageTitle()
    {
        if (static::$pageTitleCallback) {
            return call_user_func(static::$pageTitleCallback);
        }

        return app('config')->get('app.name');
    }

    /**
     * Get the path to the modules directory.
     *
     * @return string
     */
    final public function getModulesPath($path = '')
    {
        return concatenate_path($this->config('paths.modules'), $path);
    }

    public function setSystemModulesPath()
    {
        if ($this->isProduction()) {
            throw new ModularousSystemPathException;
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

    /**
     * Get Laravel app url.
     *
     * @return string
     */
    public function getAppUrl()
    {
        return $this->config('app_url');
    }

    /**
     * Get Laravel app host.
     *
     * @return string
     */
    public function getAppHost()
    {
        return parse_url($this->config('app_url'))['host'];
    }

    /**
     * Check if admin app url is set.
     */
    public function hasAdminAppUrl(): bool
    {
        return $this->config('admin_app_url') !== '' || $this->config('admin_app_path') === '';
    }

    /**
     * Get admin app url.
     *
     * @return string
     */
    public function getAdminAppUrl()
    {
        $appUrl = $this->config('app_url');
        $adminAppUrl = $this->config('admin_app_url');

        $parsedUrl = parse_url($appUrl);
        $scheme = $parsedUrl['scheme'];
        $host = $parsedUrl['host'];

        return $adminAppUrl !== '' ? $adminAppUrl : $scheme . '://admin.' . $host;
    }

    /**
     * Get admin app host.
     *
     * @return string
     */
    public function getAdminAppHost()
    {
        return parse_url($this->getAdminAppUrl())['host'];
    }

    /**
     * Check if the current url is a panel url.
     *
     * @param string|null $url
     * @return bool
     */
    public function isPanelUrl($url = null)
    {
        $host = request()->getHost();

        if ($this->hasAdminAppUrl()) {

            if ($url) {
                $host = parse_url($url)['host'];
            }

            $appAdminHost = $this->getAdminAppHost();

            return $host === $appAdminHost;
        }

        $segment = request()->segment(1);

        if ($url) {
            $parsedUrl = parse_url($url);
            $host = $parsedUrl['host'];
            $path = $parsedUrl['path'] ?? null; // /admin/settings

            if (! $segment) {
                return false;
            }
            // get the first segment, path can start with /
            $segment = explode('/', trim($path, '/'))[0];
        }

        return $segment === $this->getAdminUrlPrefix() && $host === $this->getAppHost();
    }

    /**
     * Check if a route is a modularous route via admin route name prefix.
     */
    public function isModularousRoute(string $routeName): bool
    {
        $segments = explode('.', $routeName);

        return $segments[0] === $this->getAdminRouteNamePrefix();
    }

    /**
     * Get admin route name prefix.
     *
     * @return string
     */
    public function getAdminRouteNamePrefix()
    {
        return rtrim(ltrim($this->config('admin_route_name_prefix', 'admin'), '.'), '.');
    }

    /**
     * Get admin url prefix.
     *
     * @return string
     */
    public function getAdminUrlPrefix()
    {
        return $this->hasAdminAppUrl()
            ? false
            : rtrim(ltrim($this->config('admin_app_path', 'admin'), '/'), '/');
    }

    /**
     * Get system url prefix.
     *
     * @return string
     */
    public function getSystemUrlPrefix()
    {
        return $this->config('system_prefix', 'system-settings');
    }

    /**
     * Get system route name prefix.
     *
     * @return string
     */
    public function getSystemRouteNamePrefix()
    {
        return snakeCase(studlyName($this->getSystemUrlPrefix()));
    }

    /**
     * Get translations.
     *
     * @return array
     */
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

    /**
     * Clear translations cache.
     */
    public function clearTranslations()
    {
        $cache_key = static::$translationCacheKey;

        Cache::forget($cache_key);
    }

    /**
     * Get list of enabled modules.
     */
    public function allEnabled(): array
    {
        return $this->getByStatus(true);
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
                try {
                    if (get_class_short_name(App::make($_class)) === studlyName($routeName)) {
                        $models[] = $_class;
                    }
                } catch (\Exception $e) {
                    // TODO: get only classes
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

    final public function getThemePath($dir = '')
    {
        $themeName = $this->config('app_theme');
        $themePath = $this->getVendorPath("vue/src/sass/themes/{$themeName}");

        if (! file_exists($themePath)) {
            $themePath = $this->getVendorPath("vue/src/sass/themes/customs/{$themeName}");
        }

        return concatenate_path($themePath, $dir);
    }

    /**
     * Get modularous namespace.
     *
     * @param \Nwidart\Modules\Module $module
     * @return string
     */
    public function getVendorNamespace($append = null)
    {
        return concatenate_namespace(modularousConfig('namespace'), $append);
    }

    public static function createDisableLanguageBasedPrices($callback)
    {
        self::$disableLanguageBasedPricesCallback = $callback;
    }

    public function shouldUseLanguageBasedPrices()
    {
        $result = config('modularous.use_language_based_prices', false);

        if ($result && static::$disableLanguageBasedPricesCallback) {
            $result = ! call_user_func(static::$disableLanguageBasedPricesCallback);
        }

        return $result;
    }

    public function getCurrencyForLanguageBasedPrices()
    {
        if (! $this->shouldUseLanguageBasedPrices()) {
            return false;
        }

        $provider = $this->app->make(CurrencyProviderInterface::class);
        if (! $provider->isAvailable()) {
            return false;
        }

        $locale = app()->getLocale();
        $localeCurrencies = config('modularous.language_currencies', []);
        if (array_key_exists($locale, $localeCurrencies)) {
            $currency = $provider->findByIso4217($localeCurrencies[$locale]);

            return $currency ?: false;
        }

        return false;
    }

    /**
     * Options for admin selects: each enabled module route that exposes an Eloquent model.
     * Value is the model FQCN; title is "{moduleName} - {routeName}" (display only).
     *
     * When {@code $onlyParentSegmentModels} is true, only routes whose model uses {@see HasParentSegment} are listed.
     * When {@code $onlyPageLayoutModels} is true, only routes whose model uses {@see HasPageLayout} are listed.
     *
     * @return list<array{value: string, title: string}>
     */
    public function getModuleRouteModelSelectItems(bool $onlyParentSegmentModels = false, bool $onlyPageLayoutModels = false): array
    {
        $out = [];
        foreach ($this->all() as $module) {
            $moduleName = $module->getName();
            foreach ($module->getRawRouteConfigs(null, true) as $routeConfig) {
                if (empty($routeConfig['name'])) {
                    continue;
                }
                $routeName = $routeConfig['name'];
                try {
                    $fqcn = $module->getModel($routeName, false);
                } catch (\Throwable) {
                    continue;
                }
                if (! is_string($fqcn) || ! class_exists($fqcn)) {
                    continue;
                }
                if ($onlyParentSegmentModels && ! classHasTrait($fqcn, HasParentSegment::class)) {
                    continue;
                }
                if ($onlyPageLayoutModels && ! classHasTrait($fqcn, HasPageLayout::class)) {
                    continue;
                }
                $out[] = [
                    'value' => $fqcn,
                    'title' => $moduleName . ' - ' . $routeName,
                ];
            }
        }

        usort($out, fn ($a, $b) => strcmp($a['title'], $b['title']));

        return $out;
    }

    /**
     * Resolve "ModuleName::RouteName" for a module route model class, if registered in any module config.
     */
    public function resolveTargetModuleRouteForModelClass(string $modelClass): ?string
    {
        foreach ($this->all() as $module) {
            foreach ($module->getRawRouteConfigs(null, true) as $routeConfig) {
                if (empty($routeConfig['name'])) {
                    continue;
                }
                try {
                    $fqcn = $module->getModel($routeConfig['name'], false);
                } catch (\Throwable) {
                    continue;
                }
                if ($fqcn === $modelClass) {
                    return $module->getName() . '::' . $routeConfig['name'];
                }
            }
        }

        return null;
    }
}
