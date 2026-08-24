<?php

namespace Unusualify\Modularous\View\Widgets;

use Unusualify\Modularous\Services\Connector;
use Unusualify\Modularous\View\ModularousWidget;

class MetricGroupsWidget extends ModularousWidget
{
    public $tag = 'ue-metric-groups';

    public $widgetTag = 'v-col';

    public $widgetCol = [
        'cols' => 12,
        'lg' => 12,
    ];

    public $attributes = [
        'class' => 'd-flex flex-column flex-grow-1 min-height-0 overflow-y-auto',

        'title' => 'Metrics',

        // base ue-metrics attributes
        'metricsBgHeaderColor' => 'primary-lighten-4',
        'metricsNoInline' => false,

        // base ue-metric attributes
        'metricColor' => 'primary',
        'metricCardColor' => null,
        'metricLabelColor' => 'grey-darken-1',
        'metricValueClass' => null,
        'metricLabelClass' => null,
        'metricNoInline' => true,
    ];

    private $metricsGenerated = false;

    public function hydrateAttributes($attributes)
    {
        $attributes = parent::hydrateAttributes($attributes);

        // $attributes = array_merge_recursive_preserve(
        //     modularousConfig('default_table_attributes'),
        //     $attributes
        // );

        if (isset($attributes['items'])) { // ue-metric-groups items (for each ue-metrics)
            $attributes['items'] = array_map(function ($metricGroup) {
                if (isset($metricGroup['items'])) { // ue-metrics items (for each ue-metric)
                    $metricGroup['items'] = array_map(function ($metric) {
                        if (isset($metric['connector'])) {
                            // dd(init_connector($metric['connector']));
                            $connector = new Connector($metric['connector']);

                            $connector->run($metric, 'value');
                        }

                        return $metric;
                    }, $metricGroup['items']);
                }

                return $metricGroup;
            }, $attributes['items']);
        }

        return $attributes;
    }
}
