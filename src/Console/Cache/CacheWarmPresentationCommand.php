<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Cache;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Modules\Cms\Services\CmsPublicModelResolver;
use Throwable;
use Unusualify\Modularous\Console\BaseCommand;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ModularousCache;
use Unusualify\Modularous\Jobs\Cache\WarmModuleRouteCachesJob;
use Unusualify\Modularous\Jobs\Cache\WarmPresentationItemJob;
use Unusualify\Modularous\Module;
use Unusualify\Modularous\Services\ModularousCacheService;
use Unusualify\Modularous\Support\ModularousCacheLogger;

final class CacheWarmPresentationCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected $signature = 'modularous:cache:warm-presentation
                            {--module= : Module name (StudlyCase), e.g. Blog}
                            {--route= : Route name (StudlyCase); requires --module}
                            {--locale= : Warm a single locale only}
                            {--sync : Warm synchronously instead of dispatching queue jobs}
                            {--queue : Deprecated; queue dispatch is now the default}
                            {--chunk=100 : Batch size when iterating published models}
                            {--id= : Warm a single record id}
                            {--dry-run : List what would be warmed without dispatching jobs or warming caches}';

    /**
     * @var string
     */
    protected $description = 'Warm public presentationItem URL caches for deployment (all routes or filtered)';

    public function handle(): int
    {
        if (! ModularousCache::isEnabled()) {
            $this->warn('MODULAROUS: Caching is disabled.');

            return self::FAILURE;
        }

        if (! ModularousCache::isPresentationCacheEnabled()) {
            $this->warn('MODULAROUS: Presentation cache store is disabled (MODULAROUS_PRESENTATION_CACHE_STORE=none).');

            return self::FAILURE;
        }

        $moduleFilter = $this->normalizedStudlyOption('module');
        $routeFilter = $this->normalizedStudlyOption('route');
        $locale = $this->normalizedLocaleOption();
        $recordId = $this->normalizedIdOption();
        if ($recordId === false) {
            return self::FAILURE;
        }
        $chunkSize = max(1, (int) $this->option('chunk'));
        $useQueue = $this->resolveUseQueue();
        $dryRun = (bool) $this->option('dry-run');

        if ($routeFilter !== null && $moduleFilter === null) {
            $this->error('The --route option requires --module.');

            return self::FAILURE;
        }

        $targets = $this->resolvePresentationTargets($moduleFilter, $routeFilter);

        if ($targets === []) {
            $this->warn('No module routes with presentationItem cache enabled were found.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->info('Dry run — listing targets that would be warmed.');
        } else {
            ModularousCacheLogger::info('cache.command.warm_presentation.start', [
                'module' => $moduleFilter,
                'route' => $routeFilter,
                'locale' => $locale,
                'queue' => $useQueue,
                'chunk' => $chunkSize,
                'id' => $recordId,
                'targets' => $targets,
            ]);
        }

        $queuedJobs = 0;
        $warmed = 0;
        $skipped = 0;
        $processed = 0;
        $wouldProcess = 0;

        foreach ($targets as $target) {
            $moduleName = $target['module'];
            $routeName = $target['route'];

            $module = Modularous::find($moduleName);
            if (! $module instanceof Module || ! $module->hasRoute($routeName)) {
                $this->line("  <fg=yellow>⚠</> {$moduleName} -> {$routeName}: module or route not found, skipping");

                continue;
            }

            if ($dryRun) {
                $wouldProcess += $this->reportDryRunTarget(
                    $module,
                    $moduleName,
                    $routeName,
                    $recordId,
                    $locale,
                    $useQueue,
                    $chunkSize,
                );

                continue;
            }

            $this->info("{$moduleName} -> {$routeName}: warming presentationItem caches" . ($useQueue ? ' (queue)' : ''));

            if ($useQueue && $recordId === null) {
                WarmModuleRouteCachesJob::dispatch(
                    $moduleName,
                    $routeName,
                    [
                        'counts' => false,
                        'index' => false,
                        'record' => false,
                        'formItem' => false,
                        'formattedItem' => false,
                        'presentationItem' => true,
                    ],
                    $chunkSize,
                );
                $queuedJobs++;
                $this->line('  <fg=green>✓</> Queued route warmup job');

                continue;
            }

            $query = $this->buildPublishedModelQuery($module, $routeName, $recordId);
            $total = (clone $query)->count();

            if ($total === 0) {
                $message = $recordId !== null
                    ? "No published record with id {$recordId}"
                    : 'No published records';
                $this->line("  <fg=yellow>⚠</> {$message}");

                if ($recordId !== null) {
                    return self::FAILURE;
                }

                continue;
            }

            /** @var ModularousCacheService $cacheService */
            $cacheService = app('modularous.cache');

            $progress = $this->output->isVerbose() ? null : $this->output->createProgressBar($total);
            $progress?->start();

            $query->chunk($chunkSize, function ($records) use (
                $moduleName,
                $routeName,
                $locale,
                $useQueue,
                $cacheService,
                $progress,
                &$warmed,
                &$skipped,
                &$processed,
                &$queuedJobs,
            ) {
                foreach ($records as $record) {
                    if (! $record instanceof Model || $record->getKey() === null) {
                        $skipped++;
                        $progress?->advance();

                        continue;
                    }

                    if ($useQueue) {
                        WarmPresentationItemJob::dispatch($record, $moduleName, $routeName, $locale);
                        $queuedJobs++;
                    } else {
                        $result = $cacheService->warmupPresentationItem($moduleName, $routeName, $record, $locale);
                        if ($result) {
                            $warmed++;
                        } else {
                            $skipped++;
                        }
                    }

                    $processed++;
                    $progress?->advance();
                }
            });

            $progress?->finish();
            $this->newLine();
        }

        if ($dryRun) {
            $modeLabel = $useQueue ? 'queue' : 'sync';
            $localeLabel = $locale ?? 'all';
            $this->info("Dry run complete. Would process {$wouldProcess} record(s) [{$modeLabel}, locale={$localeLabel}].");
        } elseif ($useQueue) {
            $queueName = config('modularous.cache.queue.name')
                ?? config('modularous.cache.observer.queue_name', 'modularous-cache');
            $this->info("Queued {$queuedJobs} job(s) on \"{$queueName}\".");
        } else {
            $this->info("Warmed {$warmed} record(s); skipped {$skipped}.");
        }

        if (! $dryRun) {
            ModularousCacheLogger::info('cache.command.warm_presentation.complete', [
                'module' => $moduleFilter,
                'route' => $routeFilter,
                'queue' => $useQueue,
                'queuedJobs' => $queuedJobs,
                'warmed' => $warmed,
                'skipped' => $skipped,
                'processed' => $processed,
            ]);
        }

        return self::SUCCESS;
    }

    protected function reportDryRunTarget(
        Module $module,
        string $moduleName,
        string $routeName,
        ?int $recordId,
        ?string $locale,
        bool $useQueue,
        int $chunkSize,
    ): int {
        $query = $this->buildPublishedModelQuery($module, $routeName, $recordId);
        $total = (clone $query)->count();
        $localeLabel = $locale ?? 'all';
        $modeLabel = $useQueue ? 'queue' : 'sync';

        if ($total === 0) {
            $message = $recordId !== null
                ? "No published record with id {$recordId}"
                : 'No published records';
            $this->line("  <fg=yellow>⚠</> {$moduleName} -> {$routeName}: {$message}");

            return 0;
        }

        if ($useQueue && $recordId === null) {
            $this->line("Would queue WarmModuleRouteCachesJob: {$moduleName}::{$routeName} ({$total} records) [{$modeLabel}, locale={$localeLabel}]");

            return $total;
        }

        if ($this->output->isVerbose()) {
            $count = 0;

            $query->chunk($chunkSize, function ($records) use (
                $moduleName,
                $routeName,
                $locale,
                $useQueue,
                &$count,
            ) {
                foreach ($records as $record) {
                    if (! $record instanceof Model || $record->getKey() === null) {
                        continue;
                    }

                    if ($useQueue) {
                        $localeSuffix = $locale !== null ? ", locale={$locale}" : '';
                        $this->line("Would queue WarmPresentationItemJob: {$moduleName}::{$routeName} id={$record->getKey()}{$localeSuffix} [queue]");
                    } else {
                        $localeSuffix = $locale !== null ? ", locale={$locale}" : ', locale=all';
                        $this->line("Would warmup presentationItem: {$moduleName}::{$routeName} id={$record->getKey()}{$localeSuffix} [sync]");
                    }

                    $count++;
                }
            });

            return $count;
        }

        if ($useQueue) {
            $this->line("Would queue WarmPresentationItemJob: {$moduleName}::{$routeName} ({$total} records) [{$modeLabel}, locale={$localeLabel}]");
        } else {
            $this->line("Would warmup presentationItem: {$moduleName}::{$routeName} ({$total} records) [{$modeLabel}, locale={$localeLabel}]");
        }

        return $total;
    }

    /**
     * @return list<array{module: string, route: string}>
     */
    protected function resolvePresentationTargets(?string $moduleFilter, ?string $routeFilter): array
    {
        $targets = [];

        if ($moduleFilter !== null) {
            $module = Modularous::find($moduleFilter);
            if (! $module instanceof Module) {
                $this->error("Module '{$moduleFilter}' not found.");

                return [];
            }

            $modules = [$module];
        } else {
            $modules = Modularous::allEnabled();
        }

        foreach ($modules as $module) {
            if (! $module instanceof Module) {
                continue;
            }

            $moduleName = $module->getStudlyName();

            if (! ModularousCache::isEnabled($moduleName)) {
                continue;
            }

            $routeNames = $routeFilter !== null
                ? [$routeFilter]
                : $module->getRouteNames();

            foreach ($routeNames as $routeName) {
                $routeName = Str::studly((string) $routeName);

                if ($routeFilter !== null && ! $module->hasRoute($routeName)) {
                    $this->error("Route '{$routeFilter}' not found on module '{$moduleName}'.");

                    return [];
                }

                if (! $this->isPresentationRouteTarget($moduleName, $routeName)) {
                    if ($routeFilter !== null) {
                        $this->warn("{$moduleName} -> {$routeName}: presentationItem cache is not enabled.");
                    }

                    continue;
                }

                $targets[] = [
                    'module' => $moduleName,
                    'route' => $routeName,
                ];
            }
        }

        return $targets;
    }

    protected function isPresentationRouteTarget(string $moduleName, string $routeName): bool
    {
        if (! ModularousCache::isCacheTypeConfigured($moduleName, $routeName, 'presentationItem')) {
            return false;
        }

        return ModularousCache::isEnabled($moduleName, $routeName, 'presentationItem');
    }

    /**
     * @return Builder<Model>
     */
    protected function buildPublishedModelQuery(Module $module, string $routeName, ?int $recordId): Builder
    {
        $model = $module->getModel($routeName);
        $modelClass = $model::class;

        if ($module->isSingleton($routeName)) {
            $query = $modelClass::query()->orderBy('id');

            if ($recordId !== null) {
                $query->whereKey($recordId);
            }

            return $query;
        }

        $query = $modelClass::query()->orderBy('id');

        if (class_exists(CmsPublicModelResolver::class)) {
            CmsPublicModelResolver::applyPublishedVisibilityScopes($query, $modelClass);
        }

        if ($recordId !== null) {
            $query->whereKey($recordId);
        }

        return $query;
    }

    protected function normalizedStudlyOption(string $name): ?string
    {
        $value = $this->option($name);

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return Str::studly(trim($value));
    }

    protected function normalizedLocaleOption(): ?string
    {
        $value = $this->option('locale');

        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return trim($value);
    }

    protected function normalizedIdOption(): int|false|null
    {
        $value = $this->option('id');

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            $this->error('The --id option must be numeric.');

            return false;
        }

        return (int) $value;
    }

    protected function resolveUseQueue(): bool
    {
        if ((bool) $this->option('sync')) {
            return false;
        }

        if ((bool) $this->option('queue')) {
            $this->warn('The --queue option is deprecated; queue dispatch is now the default.');
        }

        if ($this->isQueueAvailable()) {
            return true;
        }

        $connection = $this->resolveQueueConnectionName();
        $driver = (string) config("queue.connections.{$connection}.driver", $connection);
        $this->warn("Queue unavailable (driver={$driver}), warming synchronously");

        return false;
    }

    protected function resolveQueueConnectionName(): string
    {
        $connection = config('modularous.cache.queue.connection');

        if (is_string($connection) && $connection !== '') {
            return $connection;
        }

        return (string) config('queue.default', 'sync');
    }

    protected function isQueueAvailable(): bool
    {
        $connection = $this->resolveQueueConnectionName();
        $driver = (string) config("queue.connections.{$connection}.driver", $connection);

        if ($driver === 'sync' || $connection === 'sync') {
            return false;
        }

        if ($driver === 'redis') {
            try {
                $redisConnection = config("queue.connections.{$connection}.connection", 'default');
                Redis::connection(is_string($redisConnection) ? $redisConnection : 'default')->ping();

                return true;
            } catch (Throwable) {
                return false;
            }
        }

        return true;
    }
}
