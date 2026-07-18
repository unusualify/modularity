<?php

declare(strict_types=1);

namespace Unusualify\Modularous\View\Widgets;

use Unusualify\Modularous\Services\ArtisanRunner\Contracts\ArtisanRunnerInterface;
use Unusualify\Modularous\View\ModularousWidget;

class ArtisanRunnerWidget extends ModularousWidget
{
    public $tag = 'ue-artisan-runner';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 6,
    ];

    public $attributes = [
        'class' => 'h-100',
        'title' => 'Artisan Runner',
        'elevation' => 2,
        'subtitle' => 'Select a command to configure and run.',
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

        return $attributes;
    }
}
