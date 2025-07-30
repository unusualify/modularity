<?php

namespace Unusualify\Modularity\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Unusualify\Modularity\Services\MessageStage;
use Unusualify\Modularity\Entities\Demand;

class DemandController extends Controller
{
    public function index(Request $request, string $demandableType, $demandableId)
    {
        $demandable = $demandableType::find($demandableId);
        $demands = $demandable->demands;

        return response()->json([
            'variant' => MessageStage::SUCCESS,
            'data' => $demands,
        ]);
    }

    public function show(Request $request, Demand $demand)
    {
        return response()->json([
            'variant' => MessageStage::SUCCESS,
            'demand' => $demand,
        ]);
    }
}
