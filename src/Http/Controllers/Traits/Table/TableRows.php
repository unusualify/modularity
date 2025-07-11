<?php

namespace Unusualify\Modularity\Http\Controllers\Traits\Table;

use Illuminate\Support\Collection;
use Unusualify\Modularity\Entities\Enums\Permission;
use Unusualify\Modularity\Facades\Modularity;
use Unusualify\Modularity\Traits\Allowable;

trait TableRows
{
    use Allowable;

    /**
     * Get the table row actions
     *
     * @return array
     */
    protected function getTableRowActions()
    {
        $tableActions = [];

        $noDefaultTableRowActions = $this->getConfigFieldsByRoute('no_default_table_row_actions', false);

        if (! $noDefaultTableRowActions) {
            // if $this->repository has hasPayment
            if (classHasTrait($this->repository->getModel(), 'Unusualify\Modularity\Entities\Traits\HasPayment')) {
                $tableActions[] = [
                    'name' => 'pay',
                    'icon' => 'mdi-contactless-payment',
                    'forceLabel' => true,
                    // 'can' => 'pay',
                    // 'color' => 'red darken-2',
                    'color' => 'success',
                    'form' => [
                        'attributes' => [
                            'title' => [
                                'text' => 'PAYMENT AND INVOICES',
                                'tag' => 'div',
                                // 'type' => 'p',
                                'weight' => 'medium',
                                'align' => 'center',
                                'justify' => 'space-between',
                                'margin' => 'a-2',
                                'color' => 'default',
                                'classes' => 'justify-content-between',
                            ],
                            // 'systembar' => true,
                            'schema' => $this->createFormSchema($this->repository->getPaymentFormSchema()),
                            'actionUrl' => route('admin.system.system_payment.payment'),
                            'async' => false,
                        ],
                        'model_formatter' => [
                            'price_id' => 'payment_price.id', // lodash get method
                            // 'price_id' => 'paymentPrice.id', // lodash get method
                        ],
                        'schema_formatter' => [
                            'payment_service.price_object' => 'payment_price',
                            // 'payment_service.price_object' => 'paymentPrice',
                        ],
                    ],
                    'conditions' => [
                        ['state.code', 'in', ['pending-payment']],
                        // ['payable_price.total_amount', '>', 0],
                        ['payment.status', 'not in', ['COMPLETED']],
                    ],
                    //  admin.system.system_payment.payment routeName
                    //  admin.crm.template/system/system-payments/pay/{price}
                ];
                // dd($actions);
            }

            // duplicate action
            if ($this->getIndexOption('duplicate')) {
                $tableActions[] = [
                    'name' => 'duplicate',
                    // 'icon' => '$edit',
                    'color' => 'primary darken-2',
                ];
            }

            // edit action
            if ($this->getIndexOption('edit')) {
                $tableActions[] = [
                    'name' => 'edit',
                    'color' => 'primary darken-2',
                    'can' => $this->permissionPrefix(Permission::EDIT->value),
                    // 'color' => 'green darken-2',
                ];
            }

            // delete action
            if ($this->getIndexOption('delete')) {
                $tableActions[] = [
                    'name' => 'delete',
                    'can' => $this->permissionPrefix(Permission::DELETE->value),
                    'variant' => 'outlined',
                    // 'color' => 'red darken-2',
                    'color' => 'error',
                ];
            }

            // restore action
            if ($this->getIndexOption('restore')) {
                $tableActions[] = [
                    'name' => 'restore',
                    // 'icon' => '$',
                    'can' => 'restore',
                    // 'color' => 'red darken-2',
                    'color' => 'green',
                ];
            }

            // force delete action
            if ($this->getIndexOption('forceDelete')) {
                $tableActions[] = [
                    'name' => 'forceDelete',
                    'icon' => '$delete',
                    'can' => 'forceDelete',
                    // 'color' => 'red darken-2',
                    'color' => 'red',
                ];
            }

            // show action
            if ($this->getIndexOption('show')) {
                $tableActions[] = [
                    'name' => 'Show',
                    'icon' => 'mdi-eye',
                    'color' => 'info',
                    'show' => true,
                    'title' => 'Show Item',
                    'widthType' => '',
                    'except' => [
                        'actions',
                        'last_activities',
                        'activities',
                        'activities_show',
                        'lastActivities_show',
                    ],
                    'fullscreen' => true,
                ];
            }

            // activity action
            if ($this->getIndexOption('activity')) {
                $tableActions[] = [
                    'name' => 'Last Operations',
                    'icon' => 'mdi-book-open-variant',
                    'color' => 'grey-darken-2',
                    'show' => 'last_activities',
                    // 'conditions' => [
                    //     ['last_activities', '>', 0],
                    // ],
                    'title' => 'Last Operations',
                    'only' => [
                        'created_at' => 'Time',
                        'event' => 'Event',
                        'causer_id' => 'Causer ID',
                        'causer_type' => 'Causer Type',
                        'causer.name' => 'User Name',
                        // 'causer' => 'Causer',
                        'properties.attributes' => 'New Data',
                        'properties.old' => 'Previous Data',
                    ],
                    // 'except' => [
                    //     'batch_uuid',
                    // ]
                ];
            }
        }

        $tableNavigationActions = Modularity::find($this->moduleName)->getNavigationActions($this->routeName);
        $tableActionsCollection = collect($tableActions);

        foreach ($tableNavigationActions as $key => $navigationAction) {
            $mergeable = isset($navigationAction['merge']) ? (bool) $navigationAction['merge'] : false;

            if (isset($navigationAction['name']) && $mergeable) {
                $isFound = false;
                $tableActionsCollection = $tableActionsCollection->reduce(function ($acc, $action) use ($navigationAction, &$isFound) {
                    if ($action['name'] == $navigationAction['name']) {
                        $acc->push(array_merge($action, $navigationAction));
                        $isFound = true;
                    } else {
                        $acc->push($action);
                    }

                    return $acc;
                }, collect([]));

                if ($isFound) {
                    unset($tableNavigationActions[$key]);
                }
            }
        }

        $tableActions = $tableActionsCollection->toArray();

        // navigation actions
        $tableActions = array_merge(
            $tableActions,
            $tableNavigationActions
        );

        $tableActions = Collection::make($tableActions)->reduce(function ($acc, $action, $key) {
            $noSuperAdmin = $action['noSuperAdmin'] ?? false;

            // $action['is'] = true;
            // if(isset($action['connector'])){
            //     $connector = new Connector($action['connector']);

            //     $connector->run($action, 'is');
            // }
            // if(!$action['is']){
            //     return $acc;
            // }

            $isAllowed = $this->isAllowedItem(
                $action,
                searchKey: 'allowedRoles',
                orClosure: fn ($item) => ! $noSuperAdmin && $this->user->isSuperAdmin(),
            );

            if (! $isAllowed) {
                return $acc;
            }

            if (isset($action['href'])) {
                $action['href'] = resolve_route($action['href']);
            }

            if (isset($action['endpoint'])) {
                $action['endpoint'] = resolve_route($action['endpoint']);
            }

            if (isset($action['url'])) {
                $action['url'] = resolve_route($action['url']);
            }

            if (isset($action['responsive'])) {
                $action = $this->applyResponsiveClasses(
                    item: $action,
                    searchKey: 'responsive',
                    display: 'flex',
                    classNotation: 'class'
                );
            }

            $acc[] = $action;

            return $acc;
        }, []);

        // dropdown actions
        if (count($tableActions) > 3) {
            $this->tableAttributes['rowActionsType'] = 'dropdown';
        }

        return $tableActions;
    }
}
