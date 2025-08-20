<?php

use Unusualify\Modularity\View\Component;

if (! function_exists('modularity_response_modal_body_component')) {
    function modularity_response_modal_body_component($color, $icon, string $title, string $description, $redirector = null, array $modalProps = [])
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

if (! function_exists('modularity_modal_service')) {
    function modularity_modal_service(string $color, string $icon, string $title, string $description, array $modalProps = [])
    {
        return [
            'component' => 'ue-recursive-stuff',
            'props' => [
                'configuration' => modularity_response_modal_body_component($color, $icon, $title, $description, $modalProps),
            ],
            'modalProps' => $modalProps,
        ];
    }
}

if (! function_exists('modularity_modal_service_form')) {
    function modularity_modal_service_form($schema, $actionUrl, $buttonText, array $model = [], array $modalProps = [], $formProps = [])
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
