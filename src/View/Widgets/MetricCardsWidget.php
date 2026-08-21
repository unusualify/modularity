<?php

namespace Unusualify\Modularous\View\Widgets;

use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\View\ModularousWidget;

class MetricCardsWidget extends ModularousWidget
{
    public $tag = 'ue-metric-cards';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 12,
    ];

    public $attributes = [
        'class' => '',

        'title' => 'Metrics',

        'metricCol' => [
            'cols' => 12,
            'sm' => 6,
            'lg' => 3,
        ],

        'metricAttributes' => [
            'color' => 'primary',
            'labelColor' => 'grey-darken-1',
            'padDigits' => 2,
            'variant' => 'flat',
            'elevation' => 1,
            'rounded' => 'lg',
            'border' => 'opacity-8',
            'appendIconAttributes' => [
                'color' => 'grey-darken-1',
                'size' => '18',
            ],
        ],

    ];

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);
        if (isset($attributes['items'])) {
            $attributes['items'] = array_map(function ($metric) {
                if (isset($metric['connector'])) {
                    $connector = new Connector($metric['connector']);

                    if (isset($metric['pushEvents'])) {
                        $connector->pushEvents($metric['pushEvents']);
                    }

                    $connector->run($metric, 'value');
                } elseif (isset($metric['value']) && is_callable($metric['value'])) {
                    $metric['value'] = $metric['value']();
                }

                return $metric;
            }, $attributes['items']);
        }

        $attributes['endpoint'] = route('admin.modularous.metrics');

        return $attributes;
    }
}
