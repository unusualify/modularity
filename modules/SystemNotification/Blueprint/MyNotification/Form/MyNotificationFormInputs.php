<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class MyNotificationFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => 'text',
                'name' => 'message',
                'label' => 'Message',
                'editable' => 'hidden',
                'noSubmit' => true,
            ],
            [
                'type' => 'preview',
                'name' => 'messagePreview',
                'previewKey' => 'message',
                'creatable' => 'hidden',
                'noSubmit' => true,
                'default' => null,
                'col' => ['cols' => 12],
                'configuration' => [
                    'tag' => 'v-card',
                    'attributes' => [
                        'class' => 'py-4 mb-4',
                        'elevation' => 2,
                    ],
                    'slots' => [
                        'title' => [
                            'tag' => 'div',
                            'attributes' => [
                                'class' => 'text-body-large',
                            ],
                            'elements' => __('Message'),
                        ],
                        'prepend' => [
                            'tag' => 'v-icon',
                            'attributes' => [
                                'icon' => 'mdi-file-document-outline',
                                'color' => 'primary',
                            ],
                        ],
                        'text' => [
                            'tag' => 'div',
                            'attributes' => [
                                'class' => 'font-weight-bold text-wrap pt-2 text-primary',
                            ],
                            'elements' => '$messagePreview',
                        ],
                    ],
                ],
            ],
        ];
    }
}
