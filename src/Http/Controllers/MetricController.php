<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Unusualify\Modularity\Services\Connector;
use Unusualify\Modularity\Services\MessageStage;

class MetricController extends Controller
{
    public function __invoke(Request $request)
    {
        $variant = MessageStage::WARNING;
        $data = [];

        if ($request->has('date_range')) {

            $range = $request->date_range;
            $validFilter = false;
            if (is_array($range) && count($range) > 1) {
                $startDate = array_shift($range);
                $endDate = array_pop($range);
                $validFilter = true;
            } else {
                $startDate = null;
                $endDate = null;
            }

            if ($request->has('items')) {
                $variant = MessageStage::SUCCESS;

                $metrics = $request->items;
                foreach ($metrics as &$metric) {

                    if (isset($metric['connector'])) {
                        $connector = new Connector($metric['connector']);

                        $pushEvents = [];

                        if (isset($metric['connectorFilter'])) {
                            if (isset($metric['connectorFilter']['name'])) {
                                $connectorFilter = $metric['connectorFilter'];
                                $connectorFilter['args'] ??= [];

                                if ($validFilter) {
                                    if (isset($connectorFilter['passRange']) && $connectorFilter['passRange']) {
                                        $connectorFilter['args']['dateRange'] = [$startDate, $endDate];
                                    } else {
                                        if (array_key_exists('startDate', $connectorFilter['args'])) {
                                            $connectorFilter['args']['startDate'] = $startDate;
                                        } else {
                                            $connectorFilter['args'][] = $startDate;
                                        }

                                        if (array_key_exists('endDate', $connectorFilter['args'])) {
                                            $connectorFilter['args']['endDate'] = $endDate;
                                        } else {
                                            $connectorFilter['args'][] = $endDate;
                                        }
                                    }
                                    $metric['filtered'] = true;

                                    if (isset($connectorFilter['changeParameters']) && $connectorFilter['changeParameters']) {
                                        $connector->updateEventParameters($connectorFilter['name'], $connectorFilter['args']);
                                    } else {
                                        $pushEvents[] = $connectorFilter;
                                    }
                                } else {
                                    $metric['filtered'] = false;
                                }
                            }
                        }

                        if (isset($metric['pushEvents'])) {
                            if (count($pushEvents) > 0) {
                                $pushEvents = array_merge($pushEvents, $metric['pushEvents']);
                            } else {
                                $pushEvents = $metric['pushEvents'];
                            }

                            $connector->pushEvents($pushEvents);
                        }
                        $connector->run($metric, 'value');
                    } elseif (isset($metric['value']) && is_callable($metric['value'])) {
                        $metric['value'] = $metric['value']();
                    }
                }
                $data = $metrics;
            }

        }

        return new JsonResponse([
            'errors' => [],
            'data' => $data,
            'message' => 'Metrics fetched successfully',
            'variant' => $variant,
        ], 200);
    }
}
