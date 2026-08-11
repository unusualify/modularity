<?php

return [
    /**
     * Default presentation/blueprint driver when a route does not set blueprint|presentation.{field}.
     *
     * - config: module config.php routes.*.{inputs|headers|table_options} (default)
     * - class: Modules\{Module}\Blueprint\{Route}\Index|Form\{Route}{Surface}{Field}
     * - database: reserved (falls back to config until implemented)
     *
     * @see docs/src/pages/system-reference/adr-module-route-blueprint.md
     */
    'driver' => env('MODULAROUS_MODULE_ROUTE_PRESENTATION_DRIVER', 'config'),

    /**
     * When driver=class and no explicit class is set, try convention FQCNs.
     */
    'class_convention' => true,

    /**
     * Relative path under the module root for Blueprint provider classes.
     */
    'path' => 'Blueprint',
];
