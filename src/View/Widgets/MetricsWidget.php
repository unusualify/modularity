<?php

namespace Unusualify\Modularity\View\Widgets;

use Unusualify\Modularity\Exceptions\ModuleNotFoundException;
use Unusualify\Modularity\Services\Connector;
use Unusualify\Modularity\View\ModularityWidget;

class MetricsWidget extends ModularityWidget
{
    public $tag = 'ue-metrics';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 6,
    ];

    public $attributes = [
        // 'class' => 'w-100',

        'title' => 'Metrics',

        // base ue-metrics attributes
        // 'color' => 'primary-lighten-4',
        // 'cardColor' => 'primary-lighten-4',
        // 'bgHeaderColor' => 'primary-lighten-4',
        // 'filterColor' => 'primary-lighten-4',
        'noInline' => true,

        // base ue-metric attributes
        'metricAttributes' => [
            'color' => 'primary',
            'cardColor' => null,
            'labelColor' => 'grey-darken-1',
            'valueClass' => null,
            'labelClass' => null,
            'noInline' => false,
        ]
    ];

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);
        if(isset($attributes['items'])) { // ue-metric-groups items (for each ue-metrics)
            $attributes['items'] = array_map(function($metric) {
                if(isset($metric['connector'])) {
                    // dd(init_connector($metric['connector']));
                    $connector = new Connector($metric['connector']);

                    if(isset($metric['pushEvents'])) {
                        $connector->pushEvents($metric['pushEvents']);
                    }

                    $connector->run($metric, 'value');
                }

                return $metric;
            }, $attributes['items']);
        }

        $attributes['endpoint'] = route('admin.modularity.metrics');

        return $attributes;
    }

}