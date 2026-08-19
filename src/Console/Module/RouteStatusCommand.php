<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Module;

use Illuminate\Console\Command;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface;

class RouteStatusCommand extends Command
{
    protected $name = 'modularous:route:status';

    protected $description = 'List route enable/disable status per module.';

    public function handle(ModuleRouteStatusStoreInterface $statusStore): int
    {
        $enabled = Modularous::allEnabled();

        if (empty($enabled)) {
            $this->warn('No enabled modules found.');

            return 0;
        }

        $rows = [];

        foreach ($enabled as $module) {
            $statuses = $statusStore->getStatuses($module->getStudlyName());

            if (empty($statuses)) {
                $rows[] = [$module->getName(), '(no routes tracked)', ''];

                continue;
            }

            foreach ($statuses as $route => $routeEnabled) {
                $rows[] = [
                    $module->getName(),
                    $route,
                    $routeEnabled ? 'enabled' : 'disabled',
                ];
            }
        }

        $this->table(['Module', 'Route', 'Status'], $rows);

        return 0;
    }
}
