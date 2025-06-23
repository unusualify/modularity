<?php

namespace Unusualify\Modularity\Hydrates\Inputs;

class AssignmentHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'assignable_id',
        'noSubmit' => true,
        'col' => ['cols' => 12],
        'default' => null,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-assignment';

        $assigneeType = null;

        if (isset($input['assigneeType'])) {
            $assigneeType = $input['assigneeType'];

            if (! class_exists($input['assigneeType'])) {
                throw new \Exception('Assignee type ' . $input['assigneeType'] . ' does not exist');
            }

        } else {
            $module = $this->getModule(noSelfModule: true);
            $routeName = $this->getRouteName(noSelfRouteName: true);

            $assigneeType = $module->getRouteClass($routeName, 'model');
            $input['assigneeType'] = $assigneeType;
        }

        $q = $assigneeType::query();

        if (isset($input['scopeRole'])) {
            if (in_array('Spatie\Permission\Traits\HasRoles', class_uses_recursive($assigneeType))) {
                $roleModel = config('permission.models.role');
                $existingRoles = $roleModel::whereIn('name', $input['scopeRole'])->get();
                $q->role($existingRoles->map(fn ($role) => $role->name)->toArray());
            }
        }

        if (! $this->skipQueries) {
            $input['items'] = benchmark(function () use ($q) {
                return $q->get(['id', 'name']);
            }, label: '#assignment-hydrate-get-user-items', die: false, unit: 'milliseconds');
        }

        if (! isset($input['assignableType'])) {
            $assignableModel = $this->module->getRouteClass($this->routeName, 'model');
            $input['assignableType'] = $assignableModel;
        }

        $input['fetchEndpoint'] = $this->module->getRouteActionUrl($this->routeName, 'assignments', [snakeCase($this->routeName) => ':id']);
        $input['saveEndpoint'] = $this->module->getRouteActionUrl($this->routeName, 'createAssignment', [snakeCase($this->routeName) => ':id']);
        // $input['name'] = 'assignee_id';
        // add your logic

        if (isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])) {
            $input['accepted-file-types'] = $this->getAcceptedFileTypes($input['acceptedExtensions']);
            unset($input['acceptedExtensions']);
        }

        $filepondAcceptedFileTypes = isset($input['acceptedExtensions']) && is_array($input['acceptedExtensions'])
            ? $input['acceptedExtensions']
            : ['pdf', 'doc', 'docx', 'pages'];

        $acceptedFileTypes = $input['accepted-file-types']
            ?? $this->getAcceptedFileTypes($filepondAcceptedFileTypes);
        $maxAttachments = $input['max-attachments'] ?? 3;
        $input['filepond'] = modularity_format_input([
            'type' => 'filepond',
            'name' => 'attachments',
            'accepted-file-types' => $acceptedFileTypes,
            'max' => $maxAttachments,
            'noRules' => true,
        ]);

        return $input;
    }
}
