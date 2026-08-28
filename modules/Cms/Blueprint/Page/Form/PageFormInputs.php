<?php

namespace Modules\Cms\Blueprint\Page\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class PageFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            ['type' => 'switch', 'name' => 'active', 'label' => 'Active', 'translated' => true, 'trueValue' => true, 'falseValue' => false, 'isSecondary' => true],
            ['type' => 'revision', 'maxHeight' => '150px'],
            ['type' => 'text', 'name' => 'title', 'label' => 'Title', 'translated' => true, 'rules' => 'required', 'formEvents' => 'update:slugs:slugSourceValue:modelValue'],
            ['type' => 'slug', 'name' => 'slugs', 'label' => 'URL slug', 'translated' => true, 'rules' => 'required', '_moduleName' => 'Cms', '_routeName' => 'page', 'localeScoped' => true],

            ['type' => 'file', 'name' => 'documents', 'label' => 'Files', 'translated' => true],
            ['type' => 'image', 'name' => 'photos', 'label' => 'Images', 'translated' => true],
            [
                'type' => 'filepond',
                'name' => 'attachments',
                'label' => 'Fileponds',
                'translated' => true,
                'acceptedExtensions' => ['jpeg', 'jpg', 'png', 'gif', 'bmp', 'tiff', 'ico', 'webp'],
                'allowImagePreview' => true,
            ],
            [
                'type' => 'json-repeater',
                'name' => 'sessions',
                'label' => 'Sessions',
                'translated' => false,
                'asObject' => true,
                'default' => [],
                'noHeaders' => true,
                'formRowAttribute' => [
                    'noGutters' => true,
                    'class' => 'mt-6',
                ],
                'schema' => [
                    [
                        'type' => 'text',
                        'name' => 'session_title',
                        'label' => 'Session Title',
                        'type' => 'text',
                        'col' => [
                            'cols' => 6,
                            'class' => 'pr-2',
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'name' => 'session_description',
                        'label' => 'Session Description',
                        'col' => [
                            'cols' => 6,
                        ],
                    ],
                ],
            ],
            ['name' => 'layout', 'label' => 'Layout', 'type' => 'text'],
            ['name' => 'content', 'label' => 'Content', 'type' => 'textarea', 'translated' => true],
            ['name' => 'schema', 'label' => 'Schema', 'type' => 'json', 'isSecondary' => true],
        ];
    }
}
