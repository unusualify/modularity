<?php

use Unusualify\Modularous\View\Component;

if (! function_exists('modularous_response_modal_body_component')) {
    function modularous_response_modal_body_component($color, $icon, string $title, string $description, $redirector = null, array $modalProps = [])
    {
        return Component::makeDiv()
            ->setElements([
                Component::makeDiv()
                    ->setAttributes([
                        'class' => 'd-flex justify-center',
                    ])
                    ->setElements([
                        Component::makeVIcon()
                            ->setAttributes([
                                'icon' => $icon,
                                'size' => 'x-large',
                                'color' => $color,
                            ]),
                    ]),
                Component::makeUeTitle()
                    ->setAttributes([
                        'tag' => 'h3',
                        'type' => 'h3',
                        'color' => $color,
                        'weight' => 'regular',
                        'transform' => 'capitalize',
                        'justify' => 'center',
                    ])
                    ->setElements($title),
                Component::makeUeTitle()
                    ->setAttributes([
                        'type' => 'body-2',
                        'color' => 'grey-darken-1',
                        'weight' => 'regular',
                        'transform' => 'none',
                        'justify' => 'center',
                    ])
                    ->setElements($description),
            ])
            ->render();
    }
}

if (! function_exists('modularous_modal_service')) {
    function modularous_modal_service(string $color, string $icon, string $title, string $description, array $modalProps = [])
    {
        return [
            'component' => 'ue-recursive-stuff',
            'props' => [
                'configuration' => modularous_response_modal_body_component($color, $icon, $title, $description, $modalProps),
            ],
            'modalProps' => $modalProps,
        ];
    }
}

if (! function_exists('modularous_modal_service_form')) {
    function modularous_modal_service_form($schema, $actionUrl, $buttonText, array $model = [], array $modalProps = [], $formProps = [])
    {
        return [
            'component' => 'ue-recursive-stuff',
            'props' => [
                'configuration' => Component::makeUeForm()
                    ->setAttributes([
                        ...$formProps,
                        'hasSubmit' => true,
                        'rowAttribute' => [
                            'noGutters' => false,
                        ],
                        'schema' => $schema,
                        'actionUrl' => $actionUrl,
                        'buttonText' => $buttonText,
                        'modelValue' => $model,
                    ])
                    ->render(),
            ],
            'modalProps' => $modalProps,
        ];
    }
}

if (! function_exists('modularous_new_modal_service')) {
    function modularous_new_modal_service(string $color, string $icon, string $title, string $description, array $modalProps = [])
    {
        return [
            'component' => 'ue-recursive-stuff',
            'props' => [
                'configuration' => modularous_new_response_modal_body_component($color, $icon, $title, $description, $modalProps),
            ],
            'modalProps' => $modalProps,
        ];
    }
}

if (! function_exists('modularous_new_response_modal_body_component')) {
    function modularous_new_response_modal_body_component($color, $icon, string $title, string $description, $redirector = null, array $modalProps = [])
    {
        return Component::makeDiv()
            ->setElements([
                Component::makeDiv()
                    ->setAttributes([
                        'class' => 'd-flex justify-center',
                    ])
                    ->setElements([
                        Component::makeVIcon()
                            ->setAttributes([
                                'icon' => $icon,
                                'size' => '64',
                                'color' => $color,
                                'class' => 'mb-4',
                            ]),
                    ]),
                Component::makeUeTitle()
                    ->setAttributes([
                        'tag' => 'h4',
                        'type' => 'h4',
                        'color' => $color,
                        'weight' => 'regular',
                        'transform' => 'capitalize',
                        'justify' => 'center',
                    ])
                    ->setElements($title),
                Component::makeDiv()
                    ->setAttributes([
                        'class' => 'text-body-large text-medium-emphasis text-center',
                        'style' => 'white-space: pre-line;',
                    ])
                    ->setElements($description),
            ])
            ->render();
    }
}
