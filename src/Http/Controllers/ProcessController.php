<?php

namespace Unusualify\Modularity\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Unusualify\Modularity\Entities\Process;
use Unusualify\Modularity\Facades\Modularity;

class ProcessController extends Controller
{
    public function show(Request $request, Process $process)
    {
        $processableModel = App::make($process->processable_type);
        $with = ['lastHistory'];

        $eager = $request->get('eager') ?? null;

        if ($eager) {
            $with['processable'] = array_merge(['fileponds'], explode(',', $eager));
        } else {
            $with[] = 'processable.fileponds';
        }

        $process = Process::with($with)->find($process->id);

        $serializedProcess = $process->toArray();

        if (method_exists($processableModel, 'moduleName') && method_exists($processableModel, 'routeName')) {

            $module = Modularity::find($processableModel->moduleName());
            $repository = App::make($module->getRouteClass($processableModel->routeName(), 'repository'));

            $processableFields = [];
            // dd($module->getRouteInput($processableModel->routeName(), 'process', 'type'));
            $schema = $module->getRouteInputs($processableModel->routeName());

            $processableFields = [];
            if (count($schema) > 0) {
                $processInput = Arr::first($schema, function ($input) {
                    return $input['type'] == 'process';
                });

                if ($processInput && isset($processInput['schema'])) {
                    $processableFields = $repository->getFormFields($process->processable, $schema);
                    $processableFields = Arr::only($processableFields, array_map(fn ($i) => $i['name'], $processInput['schema']));
                }

                // $processableFields = array_merge($processInput);
            }

            $serializedProcess['processable'] = array_merge($serializedProcess['processable'], $processableFields);
        }

        return response()->json($serializedProcess);
    }

    public function update(Request $request, Process $process)
    {
        $processableModel = App::make($process->processable_type);

        if (method_exists($processableModel, 'moduleName') && method_exists($processableModel, 'routeName')) {
            // $moduleName = $processableModel->moduleName();
            // $routeName = $processableModel->routeName();
            $module = Modularity::find($processableModel->moduleName());
            $schema = $module->getRouteInputs($processableModel->routeName());
            $repository = App::make($module->getRouteClass($processableModel->routeName(), 'repository'));

            foreach ($schema as $input) {
                if (isset($input['type']) && $input['type'] == 'process' && isset($input['schema'])) {
                    foreach ($input['schema'] as $schemaInput) {
                        if (isset($schemaInput['name'])) {
                            $schema[] = $schemaInput;
                        }
                    }
                }
            }

            // dd($request->all(), $schema);
            $repository->update($process->processable_id, $request->all(), $schema);
        }

        $processFields = Arr::only($request->all(), $process->getFillable());
        // dd($processFields);
        if (count($processFields) > 0) {
            $process->processable->setProcessStatus($request->get('status'), $request->get('reason') ?? null);
        }

        return response()->json(['variant' => 'success', 'message' => 'Process updated successfully']);
    }
}
