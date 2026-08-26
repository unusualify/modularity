<?php

namespace Modules\SystemSetting\Blueprint\General\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class GeneralFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
            [
                'type' => '@collapsible_group',
                'typeIntTitle' => 'SMTP',
                'name' => 'smtp',
                'col' => ['cols' => 12],
                'schema' => [
                    ['name' => 'host', 'type' => 'text', 'label' => 'Host', 'placeholder' => 'smtp.example.com', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'port', 'type' => 'number-input', 'label' => 'Port', 'placeholder' => 587, 'rules' => 'nullable|integer', 'col' => ['cols' => 12, 'lg' => 6]],
                    [
                        'name' => 'encryption',
                        'type' => 'select',
                        'label' => 'Encryption',
                        'col' => ['cols' => 12, 'lg' => 6],
                        'returnObject' => false,
                        'itemTitle' => 'title',
                        'itemValue' => 'value',
                        'setFirstDefault' => true,
                        'default' => 'tls',
                        'items' => [
                            ['title' => 'TLS', 'value' => 'tls'],
                            ['title' => 'SSL', 'value' => 'ssl'],
                        ],
                        'rules' => 'nullable|string|in:,tls,ssl',
                    ],
                    ['name' => 'username', 'type' => 'text', 'label' => 'Username', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'password', 'type' => 'password', 'label' => 'Password', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'from_address', 'type' => 'text', 'label' => 'From address', 'rules' => 'nullable|email|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'from_name', 'type' => 'text', 'label' => 'From name', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'typeIntTitle' => 'Contact',
                'name' => 'contact',
                'schema' => [
                    ['name' => 'support_email', 'type' => 'text', 'label' => 'Support email', 'rules' => 'nullable|email|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'sales_email', 'type' => 'text', 'label' => 'Sales email', 'rules' => 'nullable|email|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'cc', 'type' => 'text', 'label' => 'Contact CC', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'bcc', 'type' => 'text', 'label' => 'Contact BCC', 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'typeIntTitle' => 'Locale & region',
                'name' => 'locale',
                'schema' => [
                    ['name' => 'timezone', 'type' => 'text', 'label' => 'Timezone', 'rules' => 'nullable|string|max:64'],
                    ['name' => 'default_currency', 'type' => 'text', 'label' => 'Default currency', 'rules' => 'nullable|string|max:8'],
                    ['name' => 'date_format', 'type' => 'text', 'label' => 'Date format', 'rules' => 'nullable|string|max:32'],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'typeIntTitle' => 'Analytics',
                'name' => 'analytics',
                'schema' => [
                    [
                        'name' => 'enabled',
                        'type' => 'switch',
                        'label' => 'Enable analytics',
                        'default' => false,
                        'trueValue' => true,
                        'falseValue' => false,
                        'hideDetails' => 'auto',
                        'rules' => 'nullable|boolean',
                        'col' => ['cols' => 12],
                    ],
                    ['name' => 'gtm_id', 'type' => 'text', 'label' => 'GTM container ID', 'placeholder' => 'GTM-XXXXXXX', 'rules' => 'nullable|string|max:64', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'ga_measurement_id', 'type' => 'text', 'label' => 'GA measurement ID', 'placeholder' => 'G-XXXXXXXXXX', 'rules' => 'nullable|string|max:64', 'col' => ['cols' => 12, 'lg' => 6]],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'typeIntTitle' => 'Maintenance down presets',
                'name' => 'down_presets',
                'col' => ['cols' => 12],
                'schema' => [
                    [
                        'name' => 'render',
                        'type' => 'text',
                        'label' => 'Render view',
                        'placeholder' => 'modularous::maintenance',
                        'default' => 'modularous::maintenance',
                        'hint' => 'Blade view passed to artisan down --render',
                        'rules' => 'nullable|string|max:255',
                        'col' => ['cols' => 12, 'lg' => 6],
                    ],
                    [
                        'name' => 'retry',
                        'type' => 'number-input',
                        'label' => 'Retry (seconds)',
                        'placeholder' => 60,
                        'default' => 60,
                        'hint' => 'Retry-After header for clients',
                        'col' => ['cols' => 12, 'lg' => 3],
                    ],
                    [
                        'name' => 'refresh',
                        'type' => 'number-input',
                        'label' => 'Refresh (seconds)',
                        'placeholder' => 30,
                        'default' => 30,
                        'hint' => 'Meta refresh interval on the maintenance page',
                        'col' => ['cols' => 12, 'lg' => 3],
                    ],
                    [
                        'name' => 'secret',
                        'type' => 'password',
                        'label' => 'Bypass secret',
                        'hint' => 'Secret token for artisan down --secret (overrides MODULAROUS_MAINTENANCE_SECRET when set)',
                        'rules' => 'nullable|string|max:255',
                        'col' => ['cols' => 12, 'lg' => 6],
                    ],
                ],
            ],

            [
                'type' => '@collapsible_group',
                'name' => 'site',
                'typeIntTitle' => 'Site',
                'schema' => [
                    ['name' => 'name', 'type' => 'text', 'label' => 'Site name', 'translated' => true, 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'tagline', 'type' => 'text', 'label' => 'Tagline', 'translated' => true, 'rules' => 'nullable|string|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'logo', 'type' => 'image', 'label' => 'Logo', 'translated' => true, 'col' => ['cols' => 12, 'lg' => 6], 'imageCol' => ['cols' => 12, 'lg' => 12, 'md' => 12]],
                    ['name' => 'favicon', 'type' => 'image', 'label' => 'Favicon', 'translated' => true, 'col' => ['cols' => 12, 'lg' => 6], 'imageCol' => ['cols' => 12, 'lg' => 12, 'md' => 12]],
                    ['name' => 'email', 'type' => 'text', 'label' => 'Email', 'rules' => 'nullable|email|max:255', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'phone', 'type' => 'input-phone', 'label' => 'Phone', 'rules' => 'nullable|string|max:64', 'col' => ['cols' => 12, 'lg' => 6]],
                    ['name' => 'address', 'type' => 'textarea', 'label' => 'Address', 'translated' => true, 'rules' => 'nullable|string'],
                ],
            ],
            [
                'type' => '@collapsible_wrap',
                'typeIntTitle' => 'Social Links',
                'schema' => [
                    ['type' => '@system_social_links'],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'name' => 'seo',
                'typeIntTitle' => 'SEO',
                'schema' => [
                    ['name' => 'robots_txt', 'type' => 'textarea', 'label' => 'Global robots.txt', 'rules' => 'nullable|string'],
                    ['name' => 'default_meta_title', 'type' => 'text', 'label' => 'Default meta title', 'translated' => true, 'rules' => 'nullable|string|max:255'],
                    ['name' => 'default_meta_description', 'type' => 'textarea', 'label' => 'Default meta description', 'translated' => true, 'rules' => 'nullable|string'],
                    ['name' => 'og_image', 'type' => 'image', 'label' => 'Default OG image', 'rules' => 'nullable'],
                    ['name' => 'json_schema', 'type' => 'textarea', 'label' => 'Global JSON-LD (Schema.org)', 'rules' => 'nullable|string', 'hint' => 'Raw JSON-LD object/array for the site homepage defaults'],
                ],
            ],
            [
                'type' => '@collapsible_group',
                'name' => 'scripts',
                'typeIntTitle' => 'Custom Scripts',
                'schema' => [
                    [
                        'name' => 'head',
                        'type' => 'textarea',
                        'label' => 'Head scripts',
                        'rules' => 'nullable|string',
                        'hint' => 'Raw HTML after consent/GTM and before layout CSS on every public page (verification, extra tags). Trusted admins only.',
                        'col' => ['cols' => 12],
                    ],
                    [
                        'name' => 'body',
                        'type' => 'textarea',
                        'label' => 'Body scripts',
                        'rules' => 'nullable|string',
                        'hint' => 'Raw HTML after the layout footer on every public page (tracking pixels). Trusted admins only.',
                        'col' => ['cols' => 12],
                    ],
                ],
            ],
        ];
    }
}
