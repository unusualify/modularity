<?php

namespace Unusualify\Modularous\Console\Cache;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Support\ModularousCacheLogger;

class CacheWarmCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:cache:warm
                            {module? : The module name to warm cache for}
                            {routeName? : The route name to warm cache for}
                            {--logChannel= : Log channel for cache warming (default: modularous.cache.logging.channel)}
                            {--counts : Warm only count caches}
                            {--items : Warm only item caches}
                            {--formItems : Warm only form item caches}
                            {--formattedItems : Warm only formatted item caches}
                            {--presentationItems : Warm only presentation item caches}
                            {--eager= : eager load items}
                            {--limit= : Limit the number of items to warm up}
                            ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm modularous caches for all or specific modules';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected function getLogChannel(): ?string
    {
        $channel = $this->option('logChannel');

        if (is_string($channel) && $channel !== '') {
            return $channel;
        }

        return ModularousCacheLogger::resolveChannel();
    }

    protected function configureCacheLogging(): void
    {
        ModularousCacheLogger::setChannelOverride($this->option('logChannel') ?: null);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->configureCacheLogging();

        try {
            return $this->executeWarm();
        } finally {
            ModularousCacheLogger::clearChannelOverride();
        }
    }

    protected function executeWarm(): int
    {
        $moduleName = $this->argument('module');
        $routeName = $this->argument('routeName');

        // Check if caching is enabled
        if (! ModularousCache::isEnabled()) {
            $this->warn('MODULAROUS: Caching is disabled.');

            return 1;
        }

        ModularousCacheLogger::info('cache.command.warm.start', [
            'module' => $moduleName,
            'route' => $routeName,
            'logChannel' => $this->getLogChannel(),
            'options' => [
                'counts' => (bool) $this->option('counts'),
                'items' => (bool) $this->option('items'),
                'formItems' => (bool) $this->option('formItems'),
                'formattedItems' => (bool) $this->option('formattedItems'),
                'limit' => $this->option('limit'),
            ],
        ]);

        if ($moduleName) {
            $module = Modularous::find($moduleName);
            if (! $module) {
                $this->error("Module '{$moduleName}' not found.");

                return 1;
            }

            if (! ModularousCache::isEnabled($module->getName())) {
                $this->warn("{$module->getName()}: Caching is disabled");

                return 1;
            }

            if ($routeName) {
                $this->warmModuleCache($module, $routeName);
            } else {
                collect($module->getRouteNames())->each(function ($routeName) use ($module) {
                    if (! ModularousCache::isEnabled($module->getName(), $routeName)) {
                        $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Caching is disabled", verbosity: 'v');

                        return;
                    }
                    if (ModularousCache::isEnabled($module->getName(), $routeName, 'counts')) {
                        $this->warmModuleCounts($module, $routeName);
                    } else {
                        $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Counts cache is disabled", verbosity: 'v');
                    }

                    $this->warmModuleItems($module, $routeName);
                });
            }
        } else {
            $this->warmAllModulesCache();
        }

        ModularousCacheLogger::info('cache.command.warm.complete', [
            'module' => $moduleName,
            'route' => $routeName,
        ]);

        return 0;
    }

    protected function getType(): string
    {
        $type = 'all';
        if ($this->option('counts')) {
            $type = 'counts';
        } elseif ($this->option('items')) {
        } elseif ($this->option('formItems')) {
            $type = 'formItems';
        } elseif ($this->option('formattedItems')) {
            $type = 'formattedItems';
        } elseif ($this->option('presentationItems')) {
            $type = 'presentationItems';
        }

        return $type;
    }

    /**
     * Warm cache for a specific module.
     */
    protected function warmModuleCache(string $moduleName, string $routeName): void
    {
        // Try to find the module
        $module = Modularous::find($moduleName);

        if (! $module) {
            $this->error("Module '{$moduleName}' not found.");

            return;
        }

        if (! ModularousCache::isEnabled($module->getName())) {
            $this->warn("{$moduleName}: Caching is disabled");

            return;
        }

        if (! ModularousCache::isEnabled($module->getName(), $routeName)) {
            $this->line("  <fg=yellow>⚠</> {$moduleName} -> {$routeName}: Caching is disabled");

            return;
        }

        $this->info("{$moduleName} -> {$routeName}: Warming cache");

        $type = $this->getType();

        switch ($type) {
            case 'counts':
                $this->warmModuleCounts($module, $routeName);

                break;
            case 'items':
            case 'formItems':
            case 'formattedItems':
            case 'presentationItems':
                $this->warmModuleItems($module, $routeName);

                break;
            default:
                $this->warmModuleCounts($module, $routeName);
                $this->warmModuleItems($module, $routeName);

                // $this->warmModuleAll($module, $routeName);
                break;
        }

        $this->newLine();
        $this->info("{$moduleName} -> {$routeName}: Cache warming completed");
    }

    /**
     * Warm count caches for a module.
     */
    protected function warmModuleCounts(Module $module, string $routeName): void
    {
        // Try to find and instantiate the repository for this module
        $controller = $module->getController($routeName);

        // if (! $controller || ! class_exists($controller)) {
        //     $this->line("  <fg=yellow>⚠</> No controller found for {$module}");

        //     return;
        // }

        try {
            $controller->setupDefaultFilters();
            $useUserAwareCache = $controller->getRepository()->shouldUseUserAwareCache();

            if ($useUserAwareCache) {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Repository uses user aware caching, which is not supported in warming counts", verbosity: 'vv');

                return;
            }
            $countsList = $controller->getMainCountsList();

            foreach ($countsList as $filter) {
                try {
                    $controller->handleFilterCount($filter);
                    $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warmed '{$filter['slug']}' count cache", verbosity: 'vv');
                } catch (\Exception $e) {
                    $this->line("  <fg=red>✗</> {$module->getName()} -> {$routeName}: Failed to warm '{$filter['slug']}' count: " . $e->getMessage(), verbosity: 'vv');
                }
            }

        } catch (\Exception $e) {
            $this->error("{$module->getName()} -> {$routeName}: Failed to warm caches: " . $e->getMessage(), verbosity: 'vv');
            $this->error($e->getTraceAsString(), verbosity: 'vvv');
            if (($logChannel = $this->getLogChannel())) {
                Log::channel($logChannel)->error('Cache warm COUNTS error: ' . $e->getMessage(), [
                    'module' => $module->getName(),
                    'routeName' => $routeName,
                    'exception' => $e->getTraceAsString(),
                ]);
            }

            ModularousCacheLogger::error('cache.command.warm.counts_failed', [
                'module' => $module->getName(),
                'route' => $routeName,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm index caches for a module.
     */
    protected function warmModuleItems(Module $module, string $routeName): void
    {
        try {
            $type = $this->getType();
            // Try to find and instantiate the repository for this module
            $cacheFormItem = ModularousCache::isEnabled($module->getName(), $routeName, 'formItem');
            $cacheFormattedItem = ModularousCache::isEnabled($module->getName(), $routeName, 'formattedItem');
            $cachePresentationItem = ModularousCache::isEnabled($module->getName(), $routeName, 'presentationItem');

            if ($cacheFormItem) {
                $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warming form item cache", verbosity: 'vv');
            } else {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Form item cache is disabled, skipping form item cache warming", verbosity: 'vv');
            }

            if ($cacheFormattedItem) {
                $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warming formatted item cache", verbosity: 'vv');
            } else {
                $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Formatted item cache is disabled, skipping formatted item cache warming", verbosity: 'vv');
            }

            if ($type === 'formItems') {
                $cacheFormattedItem = false;
                $cachePresentationItem = false;
            } elseif ($type === 'formattedItems') {
                $cacheFormItem = false;
                $cachePresentationItem = false;
            } elseif ($type === 'presentationItems') {
                $cacheFormItem = false;
                $cacheFormattedItem = false;
            }

            if (! $cacheFormItem && ! $cacheFormattedItem && ! $cachePresentationItem) {
                return;
            }

            $controller = $module->getController($routeName);

            $controller->preload();
            $repository = $controller->getRepository();

            $query = $repository->getModel()->orderBy('updated_at', 'desc');
            // $query = DB::table($repository->getTable())->select('id', 'created_at', 'updated_at')->orderBy('updated_at', 'desc');

            $limit = 200;
            if ($this->option('limit')) {
                $limit = intval($this->option('limit')) > 0 ? intval($this->option('limit')) : null;

                if (! ($limit && $limit > 0)) {
                    $limit = 200;
                }
            }

            if ($this->option('eager')) {
                $query = $query->with($this->option('eager'));
            }

            $count = 0;
            $callback = function ($item, $key) use ($controller, &$count, $cacheFormItem, $cacheFormattedItem, $cachePresentationItem, $module, $routeName) {
                if ($cacheFormItem) {
                    $controller->getFormItem($item->id, withoutDefaultScopes: true, item: $item);
                }
                if ($cacheFormattedItem) {
                    $controller->getFormattedIndexItem($item);
                }
                if ($cachePresentationItem) {
                    ModularousCache::refreshModelCaches($item, [
                        'presentationItem' => true,
                        'counts' => false,
                        'index' => false,
                        'record' => false,
                        'formItem' => false,
                        'formattedItem' => false,
                    ], [
                        'moduleName' => $module->getName(),
                        'moduleRouteName' => $routeName,
                    ]);
                }
                $count++;
            };

            $query->chunk($limit, function ($items) use ($callback) {
                $items->each(function ($item, $key) use ($callback) {
                    $callback($item, $key);
                });
            }, 50);

            $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Warmed {$count} item caches", verbosity: 'vv');
        } catch (\Exception $e) {
            $this->error("{$module->getName()} -> {$routeName}: Failed to warm caches: '{$e->getMessage()}'", verbosity: 'vv');
            $this->error($e->getTraceAsString(), verbosity: 'vvv');

            if (($logChannel = $this->getLogChannel())) {
                Log::channel($logChannel)->error('Cache warm ITEMS error: ' . $e->getMessage(), [
                    'module' => $module->getName(),
                    'routeName' => $routeName,
                    'exception' => $e->getTraceAsString(),
                ]);
            }

            ModularousCacheLogger::error('cache.command.warm.items_failed', [
                'module' => $module->getName(),
                'route' => $routeName,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Warm cache for all modules.
     */
    protected function warmAllModulesCache(): void
    {
        $modules = Modularous::allEnabled();

        if (count($modules) === 0) {
            $this->warn('No enabled modules found.');

            return;
        }

        $this->info('Warming caches for all modules...');
        $this->newLine();

        foreach ($modules as $module) {
            $moduleName = $module->getStudlyName();

            if (! ModularousCache::isEnabled($module->getName())) {
                $this->line("  <fg=yellow>⚠</> {$module->getName()}: Caching is disabled", verbosity: 'v');

                continue;
            }
            $this->line("  <fg=green>✓</> {$moduleName}: Processing", verbosity: 'v');

            foreach ($module->getRouteNames() as $routeName) {
                if (! ModularousCache::isEnabled($module->getName(), $routeName)) {
                    $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Caching is disabled", verbosity: 'v');

                    continue;
                } else {
                    $this->line("  <fg=green>✓</> {$module->getName()} -> {$routeName}: Caching is enabled", verbosity: 'v');
                }

                if (ModularousCache::isEnabled($module->getName(), $routeName, 'counts')) {
                    $this->warmModuleCounts($module, $routeName);
                } else {
                    $this->line("  <fg=yellow>⚠</> {$module->getName()} -> {$routeName}: Counts cache is disabled", verbosity: 'v');
                }
                $this->warmModuleItems($module, $routeName);
            }
        }

        $this->newLine();
        $this->info('Cache warming completed for all modules.');
    }
}
