<?php

declare(strict_types=1);

namespace Unusualify\Modularous\View\Widgets;

use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\Services\SystemConsole\SystemConsoleConfig;
use Unusualify\Modularous\View\ModularousWidget;

class SystemConsoleWidget extends ModularousWidget
{
    public $tag = 'ue-system-console';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 6,
    ];

    public $attributes = [
        'class' => '',
        'title' => 'System Console',
        'subtitle' => 'Maintenance mode and cache / optimize commands.',
    ];

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);

        /** @var ArtisanRunnerInterface $runner */
        $runner = app(ArtisanRunnerInterface::class);
        $user = auth()->user();

        $canAccess = $user !== null && $runner->userCanAccess($user);

        $attributes['runnerDisabled'] = ! $canAccess;
        $attributes['endpoints'] = $canAccess
            ? $runner->panelEndpoints()
            : $runner->emptyPanelEndpoints();
        $attributes['isSuperadmin'] = (bool) ($user->is_superadmin ?? false);
        $attributes['maintenanceMode'] = SystemConsoleConfig::isInMaintenance();
        $attributes['downPresets'] = SystemConsoleConfig::downPresetsForUi();
        $attributes['defaultDownPreset'] = SystemConsoleConfig::defaultDownPresetKey();
        $attributes['cacheCommands'] = SystemConsoleConfig::cacheCommandsForUi();

        return $attributes;
    }
}
