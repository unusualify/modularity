<?php

namespace Unusualify\Modularous\Hydrates\Inputs;

use Unusualify\Modularous\Entities\Enums\Permission;

class RevisionHydrate extends InputHydrate
{
    /**
     * Default values to set before hydrating
     *
     *
     * @var array
     */
    public $requirements = [
        'name' => 'revisionable_id',
        'noSubmit' => true,
        'col' => ['cols' => 12],
        'default' => null,
        /** Max height of the scrollable revision list (CSS length, e.g. 320px). */
        'maxHeight' => '320px',
        'isSecondary' => true,
    ];

    /**
     * Manipulate Input Schema Structure
     *
     * @return void
     */
    public function hydrate()
    {
        $input = $this->input;

        $input['type'] = 'input-revision';

        $snakeRouteName = snakeCase($this->routeName);

        $input['restoreEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'restoreRevision',
            [$snakeRouteName => ':id']
        );

        $input['approveEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'approveRevision',
            [$snakeRouteName => ':id']
        );

        $input['rejectEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'rejectRevision',
            [$snakeRouteName => ':id']
        );

        $input['showEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'showView',
            [$snakeRouteName => ':id']
        );

        $input['fetchEndpoint'] = $this->module->getRouteActionUrl(
            $this->routeName,
            'listRevisions',
            [$snakeRouteName => ':id']
        );

        $canApprove = false;
        $canReject = false;
        $canRestore = false;

        if ($this->module && $this->module->getRepository($this->routeName)->hasBehavior('revisions')) {
            $canApprove = $this->module->getModel($this->routeName)->usesRevisionWorkflow() && $this->module->allowedPermission(Permission::REVISION_APPROVE->value, $this->routeName);
            $canReject = $this->module->getModel($this->routeName)->usesRevisionWorkflow() && $this->module->allowedPermission(Permission::REVISION_REJECT->value, $this->routeName);
            $canRestore = $this->module->allowedPermission(Permission::REVISION_RESTORE->value, $this->routeName);
        }

        $input['canApprove'] = $canApprove;
        $input['canReject'] = $canReject;
        $input['canRestore'] = $canRestore;

        return $input;
    }
}
