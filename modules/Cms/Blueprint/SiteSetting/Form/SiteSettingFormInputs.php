<?php

namespace Modules\Cms\Blueprint\SiteSetting\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class SiteSettingFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
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
        ];
    }
}
