<?php

namespace Modules\SystemNotification\Blueprint\MyNotification\Index;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteBlueprintProvider;
use Unusualify\Modularous\ModuleRoute;
use Unusualify\Modularous\View\Component;

final class MyNotificationIndexRowActions implements ModuleRouteBlueprintProvider
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            'read' => [
                'is' => 'v-btn',
                'label' => __('Open'),
                'icon' => 'mdi-bell-check',
                'color' => 'info',
                'iconColor' => 'white',
                'variant' => 'flat',
                'componentProps' => [
                    'variant' => 'elevated',
                    'density' => 'compact',
                ],
                // 'href' => 'notification.read',
                // 'target' => '_blank',
                'conditions' => [
                    ['is_read', '=', true],
                ],
                'modalService' => [
                    'modalProps' => [
                        'title' => '$subject',
                        // 'hasCloseButton' => true,
                        'persistent' => false,
                        'noActions' => true,
                        'titleJustify' => 'center',
                    ],
                    'component' => 'ue-recursive-stuff',
                    'props' => [
                        'configuration' => Component::makeDiv()
                            ->setElements([
                                Component::makeDiv()
                                    ->setAttributes([
                                        'class' => 'd-flex flex-column align-center justify-center',
                                    ])
                                    ->setDirectives([
                                        'html' => '${html_message}$',
                                    ]),
                                Component::makeDiv()
                                    ->setAttributes([
                                        'class' => 'd-flex justify-center my-4',
                                    ])
                                    ->setElements([
                                        Component::makeVBtn()
                                            ->setAttributes([
                                                'variant' => 'elevated',
                                                'href' => '${redirector}$',
                                                'target' => '_blank',
                                            ])
                                            ->setElements('${redirector_text}$'),
                                    ]),
                            ]),
                    ],
                ],
            ],
            'unread' => [
                'is' => 'v-btn',
                'label' => __('Open'),
                'icon' => 'mdi-bell-alert',
                'color' => 'orange-darken-1',
                'iconColor' => 'white',
                'variant' => 'flat',
                'componentProps' => [
                    'variant' => 'elevated',
                    'density' => 'compact',
                ],
                'conditions' => [
                    ['is_read', '!=', true],
                ],
                'modalService' => [
                    'modalProps' => [
                        'title' => '$subject',
                        // 'hasCloseButton' => true,
                        'persistent' => false,
                        'noActions' => true,
                        'titleJustify' => 'center',
                    ],
                    'component' => 'ue-recursive-stuff',
                    'props' => [
                        'configuration' => Component::makeDiv()
                            ->setElements([
                                Component::makeDiv()
                                    ->setAttributes([
                                        'class' => 'd-flex flex-column align-center justify-center',
                                    ])
                                    ->setDirectives([
                                        'html' => '${html_message}$',
                                    ]),
                                Component::makeDiv()
                                    ->setAttributes([
                                        'class' => 'd-flex justify-center my-4',
                                    ])
                                    ->setElements([
                                        Component::makeVBtn()
                                            ->setAttributes([
                                                'variant' => 'elevated',
                                                'href' => '${redirector}$',
                                                'target' => '_blank',
                                            ])
                                            ->setElements('${redirector_text}$'),
                                    ]),
                            ]),
                    ],
                ],
                'preProcesses' => [
                    [
                        'type' => 'put',
                        'payload' => [
                            'read_at' => '$(new Date())$',
                        ],
                        'conditions' => [
                            ['is_mine', '=', true],
                        ],
                    ],
                ],
            ],
            // 'delete' => [
            //     'name' => 'delete',
            //     'label' => 'Delete',
            //     'icon' => 'mdi-delete',
            //     'color' => 'error',
            //     'variant' => 'outlined',
            // ]
        ];
    }
}
