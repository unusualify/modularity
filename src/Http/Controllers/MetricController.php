<?php

namespace Unusualify\Modularity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Modules\PressRelease\Entities\PressRelease;
use Unusualify\Modularity\Services\Connector;
use Unusualify\Modularity\Services\MessageStage;

class MetricController extends Controller
{
    public function __invoke(Request $request)
    {
        $variant = MessageStage::WARNING;
        $data = [];

        if($request->has('date_range')
            && is_array($request->date_range)
            && count($request->date_range) > 1) {

            $range = $request->date_range;
            $startDate = array_shift($range);
            $endDate = array_pop($range);

            if($request->has('items')) {
                $variant = MessageStage::SUCCESS;

                $metrics = $request->items;
                foreach($metrics as &$metric) {

                    if(isset($metric['connector'])) {
                        $connector = new Connector($metric['connector']);

                        $pushEvents = [];
                        if(isset($metric['connectorFilter'])) {
                            if(isset($metric['connectorFilter']['name'])) {
                                $connectorFilter = $metric['connectorFilter'];
                                $connectorFilter['args'] ??= [];

                                if(array_key_exists('startDate', $connectorFilter['args'])) {
                                    $connectorFilter['args']['startDate'] = $startDate;
                                }else{
                                    $connectorFilter['args'][] = $startDate;
                                }

                                if(array_key_exists('endDate', $connectorFilter['args'])) {
                                    $connectorFilter['args']['endDate'] = $endDate;
                                }else{
                                    $connectorFilter['args'][] = $endDate;
                                }
                                $pushEvents[] = $connectorFilter;

                            }
                        }
                        if(isset($metric['pushEvents'])) {
                            if(count($pushEvents) > 0) {
                                $pushEvents = array_merge($pushEvents, $metric['pushEvents']);
                            }else{
                                $pushEvents = $metric['pushEvents'];
                            }
                            $connector->pushEvents($pushEvents);
                        }
                        $connector->run($metric, 'value');
                    } else if(isset($metric['value']) && is_callable($metric['value'])) {
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