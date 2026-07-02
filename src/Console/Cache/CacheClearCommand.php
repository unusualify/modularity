<?php

namespace Unusualify\Modularous\Console\Cache;

use Illuminate\Support\Str;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;

class CacheClearCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modularous:cache:clear
                            {module? : The module name to clear cache for}
                            {routeName? : The route name to clear cache for}
                            {--counts : Clear only count caches}
                            {--index : Clear only index/list caches}
                            {--records : Clear only record caches}
                            {--formattedItems : Clear only formatted item caches}
                            {--formItems : Clear only form item caches}
                            {--presentationItems : Clear only presentation item caches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear modularous caches for all or specific modules';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module = $this->argument('module');
        $routeName = $this->argument('routeName');
        $countsOnly = $this->option('counts');
        $indexOnly = $this->option('index');
        $recordsOnly = $this->option('records');
        $formattedItemsOnly = $this->option('formattedItems');
        $formItemsOnly = $this->option('formItems');
        $presentationItemsOnly = $this->option('presentationItems');
        // Check if caching is enabled
        if (! ModularousCache::isEnabled()) {
            $this->warn('Modularous caching is disabled.');

            return 1;
        }

        // Determine what to clear
        $clearAll = ! $countsOnly && ! $indexOnly && ! $recordsOnly && ! $formattedItemsOnly && ! $formItemsOnly && ! $presentationItemsOnly;

        if ($module && $routeName) {
            $this->clearModuleCache(Str::studly($module), Str::studly($routeName), $clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly, $presentationItemsOnly);
        } elseif ($module) {
            $this->clearModuleCache(Str::studly($module), null, $clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly, $presentationItemsOnly);
        } else {
            $this->clearAllModulesCache($clearAll, $countsOnly, $indexOnly, $recordsOnly, $formattedItemsOnly, $formItemsOnly, $presentationItemsOnly);
        }

        return 0;
    }

    /**
     * Clear cache for a specific module.
     */
    protected function clearModuleCache(
        string $module,
        ?string $routeName,
        bool $clearAll,
        bool $countsOnly,
        bool $indexOnly,
        bool $recordsOnly,
        bool $formattedItemsOnly,
        bool $formItemsOnly,
        bool $presentationItemsOnly = false,
    ): void {
        $this->info("Clearing cache for module: {$module}");
        if ($routeName) {
            $this->info("Clearing cache for route: {$routeName}");
        }

        if ($clearAll) {
            ModularousCache::invalidateModule($module, $routeName);
            $this->line("  <fg=green>✓</> All caches cleared for {$module}");

            return;
        }

        if ($countsOnly) {
            ModularousCache::invalidateCountCaches($module, $routeName);
            $this->line("  <fg=green>✓</> Count caches cleared for {$module}");
        }

        if ($indexOnly) {
            ModularousCache::invalidateIndexCaches($module, $routeName);
            // ModularousCache::invalidateIndexResponseCaches($module, $routeName);
            $this->line("  <fg=green>✓</> Index caches cleared for {$module}");
        }

        if ($recordsOnly) {
            $prefix = ModularousCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ':record:*';
            $count = ModularousCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} record caches cleared for {$module}");
        }

        if ($formattedItemsOnly) {
            $prefix = ModularousCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ':formattedItem:*';
            $count = ModularousCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} formatted item caches cleared for {$module}");
        }

        if ($formItemsOnly) {
            $prefix = ModularousCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ':formItem:*';
            $count = ModularousCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} form item caches cleared for {$module}");
        }

        if ($presentationItemsOnly) {
            $prefix = ModularousCache::getPrefix();
            $pattern = "{$prefix}:{$module}:" . ($routeName ?: '*') . ':presentationItem:*';
            $count = ModularousCache::invalidateByPattern($pattern);
            $this->line("  <fg=green>✓</> {$count} presentation item caches cleared for {$module}");
        }
    }

    /**
     * Clear cache for all modules.
     */
    protected function clearAllModulesCache(
        bool $clearAll,
        bool $countsOnly,
        bool $indexOnly,
        bool $recordsOnly,
        bool $formattedItemsOnly,
        bool $formItemsOnly,
        bool $presentationItemsOnly = false,
    ): void {
        if ($clearAll) {
            $this->info('Clearing all modularous caches...');
            ModularousCache::flush();
            $this->line('<fg=green>✓</> All modularous caches cleared');

            return;
        }

        $modules = Modularous::allEnabled();

        if ($modules->isEmpty()) {
            $this->warn('No enabled modules found.');

            return;
        }

        $this->info('Clearing caches for all modules...');

        foreach ($modules as $module) {
            $moduleName = $module->getStudlyName();

            if ($countsOnly) {
                ModularousCache::invalidateCountCaches($moduleName);
                $this->line("  <fg=green>✓</> Count caches cleared for {$moduleName}");
            }

            if ($indexOnly) {
                ModularousCache::invalidateIndexCaches($moduleName);
                // ModularousCache::invalidateIndexResponseCaches($moduleName);
                $this->line("  <fg=green>✓</> Index caches cleared for {$moduleName}");
            }

            if ($recordsOnly) {
                $prefix = ModularousCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:record:*";
                $count = ModularousCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} record caches cleared for {$moduleName}");
            }

            if ($formattedItemsOnly) {
                $prefix = ModularousCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:formattedItem:*";
                $count = ModularousCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} formatted item caches cleared for {$moduleName}");
            }

            if ($formItemsOnly) {
                $prefix = ModularousCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:formItem:*";
                $count = ModularousCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} form item caches cleared for {$moduleName}");
            }

            if ($presentationItemsOnly) {
                $prefix = ModularousCache::getPrefix();
                $pattern = "{$prefix}:{$moduleName}:*:presentationItem:*";
                $count = ModularousCache::invalidateByPattern($pattern);
                $this->line("  <fg=green>✓</> {$count} presentation item caches cleared for {$moduleName}");
            }
        }

        $this->newLine();
        $this->info('Cache clearing completed.');
    }
}
