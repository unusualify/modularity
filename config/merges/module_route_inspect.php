<?php

return [
    /**
     * Enable the Module Route Inspect admin panel + JSON API.
     */
    'enabled' => env('MODULAROUS_MODULE_ROUTE_INSPECT_ENABLED', false),

    /**
     * Who can open the Module Route Inspect panel (page + API).
     *
     * @var list<string>
     */
    'allowed_roles' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('MODULAROUS_MODULE_ROUTE_INSPECT_ALLOWED_ROLES', 'superadmin'))
    ))),

    /**
     * Persistence driver for module route enable/disable statuses.
     *
     * - filesystem: {module}/routes_statuses.json via ModuleActivator (default)
     * - database: um_module_route_statuses (env-specific; not git-tracked)
     *
     * Runtime ({@see \Unusualify\Modularous\Module::enableRoute} / isEnabledRoute)
     * and the inspect panel both resolve {@see \Unusualify\Modularous\Services\ModuleRouteInspect\Contracts\ModuleRouteStatusStoreInterface}.
     */
    'driver' => env('MODULAROUS_MODULE_ROUTE_STATUS_DRIVER', 'filesystem'),

    /**
     * Allow toggling route enable/disable from the panel (writes via status store).
     */
    'allow_status_toggle' => env('MODULAROUS_MODULE_ROUTE_INSPECT_ALLOW_STATUS_TOGGLE', true),

    /**
     * Allow running allowlisted remake heals from the panel (POST heal).
     * Default false — CLI --suggest / --heal remains available without this flag.
     * Never runs on admin document hot path (sidebar / controllers).
     */
    'allow_heal' => env('MODULAROUS_MODULE_ROUTE_INSPECT_ALLOW_HEAL', false),

    /**
     * Feature keys highlighted as dedicated CLI / UI columns.
     * Remaining present features appear under "Other".
     *
     * @var list<string>
     */
    'table_feature_keys' => [
        'translation',
        'singular',
        'cmr',
        'revisions',
        'publishable',
        'slug',
    ],
];
