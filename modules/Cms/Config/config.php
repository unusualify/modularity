<?php

use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Http\Controllers\CmsSitemapPanelController;
use Modules\Cms\Http\Controllers\SitemapController;
use Modules\Cms\Repositories\SitemapRepository;
use Modules\Cms\Routes\web;

return [
    'name' => 'Cms',
    'system_prefix' => true,
    'group' => 'system',
    'headline' => 'CMS',
    'url' => 'cms',
    'icon' => 'mdi-folder-cog-outline',

    'promotion' => [
        'enabled' => modularousConfig('cms_promotion.enabled', true),
        'scope' => modularousConfig('cms_promotion.scope', []),
        'approval' => modularousConfig('cms_promotion.approval', []),
    ],

    'routes' => [
        'style_sheet' => [
            'name' => 'StyleSheet',
            'headline' => 'Style Sheets',
            'url' => 'style-sheets',
            'route_name' => 'style_sheet',
            'icon' => 'mdi-palette-swatch-outline',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Slug',
                    'key' => 'slug',
                    'searchable' => true,
                ],
                [
                    'title' => 'Driver',
                    'key' => 'driver',
                    'searchable' => true,
                ],
                [
                    'title' => 'Framework source',
                    'key' => 'framework_source',
                    'searchable' => true,
                ],
                [
                    'title' => 'Compiled',
                    'key' => 'compiled_at',
                    'formatter' => [
                        'date',
                        'long',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'slug', 'label' => 'Slug', 'type' => 'text', 'rules' => 'nullable|string|max:191'],
                [
                    'name' => 'driver',
                    'label' => 'Driver',
                    'type' => 'select',
                    'items' => ['custom', 'bootstrap', 'tailwind', 'hybrid'],
                    'rules' => 'required|string|in:custom,bootstrap,tailwind,hybrid',
                ],
                [
                    'name' => 'framework_source',
                    'label' => 'Framework source',
                    'type' => 'select',
                    'items' => ['cdn', 'vendor', 'build'],
                    'rules' => 'nullable|string|in:cdn,vendor,build',
                ],
                ['name' => 'framework_version', 'label' => 'Framework version', 'type' => 'text', 'rules' => 'nullable|string|max:64'],
                [
                    'name' => 'definition',
                    'label' => 'Definition',
                    'type' => 'json-field',
                    'default' => (object) [],
                    'rules' => 'nullable|array',
                    'hint' => 'Geçerli JSON. Compile çıktısı: :root (root) + utility sınıfları (utilities) + ham blok (raw_css). Framework `<link>` sırası ayrıca framework + tablodaki Driver alanlarından hesaplanır. Tam örnek: uygulama kökünde config/cms_stylesheet_examples/definition.full_vendor_bootstrap_utilities.json (yenilemek için: php scripts/generate-full-cms-stylesheet-definition.php).',
                    'persistentHint' => true,
                    'jsonFieldSubtitle' => 'Bu nesne dört ana bölüm taşır — hepsi isteğe bağlı kombinlenebilir: root tasarım tokenları için (→ :root), utilities otomatik utility sınıfları için, raw_css serbest CSS için, framework harici stylesheet href sırası ve özel prelude/append için.',
                    'jsonFieldGuideTitle' => 'Definition yapısı',
                    'jsonFieldVariantChips' => [
                        [
                            'label' => 'root',
                            'detail' => 'Düz anahtar → değer haritası. Anahtarlar `--token` şeklinde normalize edilir. Boş değerler atlanır. Çıktı tek bir `:root { ... }` kuralı.',
                        ],
                        [
                            'label' => 'utilities',
                            'detail' => 'UtilityCssGenerator: çoğu grup `scale` (sınıf soneki → güvenilir CSS değeri) ve `prefix` kullanır; `spacing` için `properties` dizisi (padding | margin | gap). flex/outline/gölge için `presets` ile blok verilebilir. Desteklenen tüm anahtarlar için UtilityCssGenerator sınıfındaki blok listesine bakın.',
                        ],
                        [
                            'label' => 'raw_css',
                            'detail' => 'Derlenmiş CSS çıktısına doğrudan eklenen dizge (`cms_stylesheets.max_raw_css_bytes` ile limitlenebilir).',
                        ],
                        [
                            'label' => 'framework',
                            'detail' => '`prelude` / `prepend` / `append` / `link_hrefs` href dizileri. `inline_vendor_bootstrap_into_bundle`: Bootstrap CSS tek `<link>` (public bundle) içinde birleşsin, ayrı vendor `<link>` kalksın (yerel `/` dosyası). Tailwind build/vendor: `tailwind_css_href` veya `build_css_href_fallback`. Bootstrap vendor: sürüm haritası / `bootstrap_css_href` / fallback. Script alanları: `FrameworkScriptResolver` doc.',
                        ],
                    ],
                    'jsonFieldGuideSections' => [
                        [
                            'title' => 'Minimal gövde örneği',
                            'body' => implode("\n", [
                                '{',
                                '  "root": { "brand-primary": "#2c6bff" },',
                                '  "utilities": {',
                                '    "spacing": {',
                                '      "scale": { "1": "0.25rem" },',
                                '      "properties": ["padding", "gap"]',
                                '    }',
                                '  },',
                                '  "raw_css": ".announcement { letter-spacing: 0.02em; }",',
                                '  "framework": {',
                                '    "prepend": [],',
                                '    "append": [],',
                                '    "link_hrefs": []',
                                '  }',
                                '}',
                            ]),
                        ],
                        [
                            'title' => 'Varyasyonlar',
                            'body' => implode("\n", [
                                '• Sadece tokenlar: dolu root, utilities boş nesne {}.',
                                '• Sadece araçlar: root {} veya yok bırak; fontSize / display / renk blokları tek başına kullanılabilir.',
                                '• Çerçevesiz özelleştirme: Driver=custom; prepend/link_hrefs ile bağlantılar veya tamamen boş.',
                            ]),
                        ],
                    ],
                    'jsonSnippets' => [
                        [
                            'label' => 'Stylesheet iskeleti',
                            'hint' => 'Dört anahtarlı iskelet',
                            'insert' => [
                                'root' => (object) [],
                                'utilities' => (object) [],
                                'raw_css' => '',
                                'framework' => (object) [],
                            ],
                        ],
                        [
                            'label' => 'Utilities · spacing başlangıç',
                            'hint' => 'padding + gap, boş ölçek',
                            'insert' => [
                                'utilities' => [
                                    'spacing' => [
                                        'scale' => (object) [],
                                        'properties' => ['padding', 'gap'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                ['name' => 'scss_source', 'label' => 'SCSS (optional; enable scssphp + install package)', 'type' => 'textarea', 'rules' => 'nullable|string'],
            ],
        ],
        'layout_builder' => [
            'name' => 'LayoutBuilder',
            'headline' => 'Layout Builders',
            'url' => 'layout-builders',
            'route_name' => 'layout_builder',
            'icon' => 'mdi-page-layout-header-footer',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Slug',
                    'key' => 'slug',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Blade Source',
                    'key' => 'blade_source',
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'rules' => 'required|string|max:255',
                ],
                [
                    'name' => 'slug',
                    'label' => 'Layout slug',
                    'type' => 'text',
                    'rules' => 'required|string|max:191',
                ],
                [
                    'type' => 'select',
                    'name' => 'blade_source',
                    'label' => 'Blade source',
                    'rules' => 'required|string|in:db,filesystem',
                    'items' => [
                        ['id' => 'db', 'name' => 'Database segments (head/body/footer)'],
                        ['id' => 'filesystem', 'name' => 'Filesystem Blade view'],
                    ],
                    'default' => 'db',
                    'itemValue' => 'id',
                    'itemTitle' => 'name',
                ],
                [
                    'type' => 'text',
                    'name' => 'blade_view_name',
                    'label' => 'Filesystem view name',
                    'hint' => 'Example: vendor.modularous.cms.layout-builder.master (run php artisan cms:layout-builder:publish-blades).',
                    'rules' => 'nullable|string|max:512',
                ],
                [
                    'type' => 'select',
                    'name' => 'style_sheet_id',
                    'label' => 'Primary stylesheet',
                    'rules' => 'nullable',
                    'connector' => 'Cms:StyleSheet|repository',
                ],
                [
                    'type' => 'layout-blades',
                    'name' => 'blade_segments',
                    'label' => 'Blade segments',
                    'rules' => 'nullable|array',
                ],
                [
                    'type' => 'json-field',
                    'name' => 'definition',
                    'label' => 'Definition (JSON)',
                    'rules' => 'nullable|array',
                    'jsonFieldSubtitle' => 'Optional metadata only (filesystem layouts keep markup in Blade files).',
                    'rows' => 10,
                    'jsonSnippets' => [
                        [
                            'label' => '{}',
                            'insert' => (object) [],
                        ],
                    ],
                ],
                [
                    'type' => 'json-field',
                    'name' => 'style_sheet_slugs',
                    'label' => 'Extra stylesheet slugs (JSON array)',
                    'rules' => 'nullable|array',
                    'jsonFieldSubtitle' => 'List of style_sheets.slug values appended after the primary Style sheet FK.',
                    'rows' => 6,
                    'jsonSnippets' => [
                        [
                            'label' => '["example-slug"]',
                            'insert' => [],
                        ],
                    ],
                ],

            ],
        ],
        /**
         * Locale-agnostic presentation shell per module-route model ({@see PageLayout}).
         * Panel editors now work with a single {@code layout-blades} block that persists into {@code blade_segments}
         * when the Blade source is {@code db}.
         */
        'page_layout' => [
            'name' => 'PageLayout',
            'headline' => 'Presentation shells',
            'url' => 'page-layouts',
            'route_name' => 'page_layout',
            'icon' => 'mdi-page-layout-body',
            'title_column_key' => 'target_model_class',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
            ],
            'headers' => [
                ['title' => 'Model', 'key' => 'target_model_class', 'searchable' => true],
                ['title' => 'Label', 'key' => 'admin_label', 'searchable' => true],
                ['title' => 'Layout', 'key' => 'layoutBuilder', 'itemTitle' => 'name', 'searchable' => false],
                [
                    'title' => 'Blade Source',
                    'key' => 'blade_source',
                    'searchable' => true,
                ],
                ['title' => 'Enabled', 'key' => 'enabled', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Sort', 'key' => 'sort_order'],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'numeric-full',
                    ],
                ],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                [
                    'type' => 'module-route-model',
                    'name' => 'target_model_class',
                    'label' => 'Module / route (model)',
                    'rules' => 'required|string|max:512',
                    'onlyPageLayoutModels' => true,
                    'hint' => 'One shell per routed model class. URLs stay in Public route registry; use CmsPageLayoutResolver / LayoutBladeResolver on the front.',
                ],
                ['name' => 'admin_label', 'label' => 'Admin label', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                [
                    'type' => 'select',
                    'name' => 'layout_builder_id',
                    'label' => 'Layout builder (shell)',
                    'rules' => 'nullable|integer|exists:layout_builders,id',
                    'connector' => 'Cms:LayoutBuilder|repository',
                ],
                [
                    'type' => 'select',
                    'name' => 'blade_source',
                    'label' => 'Blade source',
                    'rules' => 'required|string|in:db,filesystem',
                    'items' => [
                        ['id' => 'db', 'name' => 'Database segments (head/body/footer)'],
                        ['id' => 'filesystem', 'name' => 'Filesystem Blade view'],
                    ],
                    'default' => 'db',
                    'itemValue' => 'id',
                    'itemTitle' => 'name',
                ],
                [
                    'type' => 'text',
                    'name' => 'blade_view_name',
                    'label' => 'Filesystem view name',
                    'hint' => 'Provide this when Blade source is filesystem (example: cms.page-layouts.shell).',
                    'rules' => 'nullable|string|max:512',
                ],
                [
                    'type' => 'layout-blades',
                    'name' => 'blade_segments',
                    'label' => 'Page layout shell segments',
                    'hint' => 'Defines the head/body/footer shell that the page renders when Blade source is database-backed.',
                    'layoutBladesSubtitle' => 'Use head/body/footer fragments when Blade source is db; leave empty for filesystem views.',
                    'rules' => 'nullable|array',
                ],
                ['name' => 'enabled', 'label' => 'Enabled', 'type' => 'switch', 'trueValue' => true, 'falseValue' => false],
                ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ],
        ],
        'parent_segment' => [
            'name' => 'ParentSegment',
            'headline' => 'Public route registry',
            'url' => 'parent-segments',
            'route_name' => 'parent_segment',
            'icon' => 'mdi-source-branch',
            'title_column_key' => 'target_model_class',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
            ],
            'headers' => [
                ['title' => 'Model', 'key' => 'target_model_class', 'searchable' => true],
                ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
                ['title' => 'Prefix', 'key' => 'normalized_prefix', 'searchable' => true],
                ['title' => 'Label', 'key' => 'admin_label', 'searchable' => true],
                ['title' => 'Enabled', 'key' => 'enabled', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Sort', 'key' => 'sort_order'],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                [
                    'type' => 'module-route-model',
                    'name' => 'target_model_class',
                    'label' => 'Module / route (model)',
                    'rules' => 'required|string|max:512',
                    'onlyParentSegmentModels' => true,
                ],
                ['name' => 'locale', 'label' => 'Locale (empty = all locales)', 'type' => 'text', 'rules' => 'nullable|string|max:12'],
                ['name' => 'normalized_prefix', 'label' => 'URL path prefix (empty = homepage / locale root)', 'type' => 'text', 'rules' => 'nullable|string|max:2048'],
                ['name' => 'admin_label', 'label' => 'Admin label', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                ['name' => 'enabled', 'label' => 'Enabled', 'type' => 'switch', 'trueValue' => true, 'falseValue' => false],
                ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
            ],
        ],
        'site_setting' => [
            'name' => 'SiteSetting',
            'headline' => 'Site Settings',
            'url' => 'site-settings',
            'route_name' => 'site_setting',
            'icon' => 'mdi-cog-sync-outline',
            'title_column_key' => 'site.name',
            'inputs' => [
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
            ],
        ],
        'redirect' => [
            'name' => 'Redirect',
            'headline' => 'Redirects',
            'url' => 'redirects',
            'route_name' => 'redirect',
            'icon' => 'mdi-directions-fork',
            'title_column_key' => 'from_path',
            'headers' => [
                ['title' => 'Locale', 'key' => 'locale', 'searchable' => true],
                ['title' => 'From', 'key' => 'from_path', 'searchable' => true],
                ['title' => 'To', 'key' => 'to_path', 'searchable' => true],
                ['title' => 'Status', 'key' => 'status_code'],
                ['title' => 'Active', 'key' => 'is_active', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                ['name' => 'from_path', 'label' => 'From path', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'to_path', 'label' => 'To path', 'type' => 'text', 'rules' => 'required'],
                ['name' => 'locale', 'label' => 'Locale', 'type' => 'select', 'items' => getLocales(), 'rules' => 'required'],
                ['name' => 'status_code', 'label' => 'Status Code', 'type' => 'number', 'rules' => 'required|integer|in:301,302,307,308'],
                ['name' => 'is_active', 'label' => 'Active', 'type' => 'switch'],
            ],

            // CSV bulk sheet: default tool_key is derived from module + route (e.g. cms.redirect); override with tool_key if needed.
            'bulk_sheet' => [
                'export_download_filename' => 'redirects-export.csv',
                'step_up_ability' => 'redirect.bulk_import',
                'preview_table_columns' => [
                    ['title' => 'Line', 'key' => 'line', 'width' => '72px'],
                    ['title' => 'OK', 'key' => 'valid', 'sortable' => false],
                    ['title' => 'Action', 'key' => 'action'],
                    ['title' => 'Locale', 'key' => 'locale'],
                    ['title' => 'From', 'key' => 'from_path'],
                    ['title' => 'To', 'key' => 'to_path'],
                    ['title' => 'Errors', 'key' => 'errors', 'sortable' => false],
                    ['title' => 'Warnings', 'key' => 'warnings', 'sortable' => false],
                ],
                'api_route_names' => [
                    'dryRun' => 'bulk.dryRun',
                    'commit' => 'bulk.commit',
                    'export' => 'bulk.export',
                ],
            ],
        ],
        /**
         * Panel Inertia index ({@code Cms/Sitemap/Index}): item table + dry-run + commit; {@see SitemapRepository},
         * {@see SitemapController}, {@see CmsSitemapPanelController}, {@see web}.
         */
        'sitemap' => [
            'name' => 'Sitemap',
            'headline' => 'Sitemap',
            'url' => 'sitemap',
            'route_name' => 'sitemap',
            'icon' => 'mdi-sitemap',
            'title_column_key' => 'id',
            'headers' => [
                ['title' => 'ID', 'key' => 'id', 'searchable' => true],
                ['title' => 'Slug', 'key' => 'slug', 'searchable' => true],
                ['title' => 'Created At', 'key' => 'created_at', 'searchable' => true],
                ['title' => 'Updated At', 'key' => 'updated_at', 'searchable' => true],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [],
        ],
        'homepage_test' => [
            'name' => 'HomepageTest',
            'headline' => 'Homepage Tests',
            'url' => 'homepage-tests',
            'route_name' => 'homepage_test',
            'icon' => 'mdi-test-tube',
            'title_column_key' => 'name',
            'table_options' => [
                'createOnModal' => true,
                'editOnModal' => true,
                'isRowEditing' => false,
                'rowActionsType' => 'inline',
            ],
            'headers' => [
                [
                    'title' => 'Name',
                    'key' => 'name',
                    'formatter' => [
                        'edit',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Status',
                    'key' => 'published',
                    'formatter' => [
                        'switch',
                    ],
                ],
                [
                    'title' => 'Created Time',
                    'key' => 'created_at',
                    'formatter' => [
                        'date',
                        'long',
                    ],
                    'searchable' => true,
                ],
                [
                    'title' => 'Actions',
                    'key' => 'actions',
                    'sortable' => false,
                ],
            ],
            'inputs' => [
                [
                    'type' => 'text',
                    'name' => 'name',
                    'label' => 'Name',
                    'translated' => true,
                ],
            ],
        ],
        'page' => [
            'name' => 'Page',
            'headline' => 'Pages',
            'url' => 'pages',
            'route_name' => 'page',
            'icon' => 'mdi-test-tube',
            'title_column_key' => 'title',
            'table_options' => [
                'includeScheduledInList' => true,
                'editOnModal' => false,
            ],
            'headers' => [
                ['title' => 'Title', 'key' => 'title', 'searchable' => true],
                ['title' => 'Published', 'key' => 'published', 'formatter' => [
                    0 => 'switch',
                    1 => [
                        'trueValue' => true,
                        'falseValue' => false,
                    ],
                ]],
                ['title' => 'Actions', 'key' => 'actions', 'sortable' => false],
            ],
            'inputs' => [
                // ['type' => 'switch', 'name' => 'published', 'label' => 'Published', 'trueValue' => true, 'falseValue' => false, 'isEvent' => true],
                // ['name' => 'publish_start_date', 'label' => 'Publish from', 'type' => 'date', 'isSecondary' => true],
                // ['name' => 'publish_end_date', 'label' => 'Publish until', 'type' => 'date', 'isSecondary' => true],
                ['type' => 'switch', 'name' => 'active', 'label' => 'Active', 'translated' => true, 'trueValue' => true, 'falseValue' => false, 'isSecondary' => true],
                ['type' => 'revision', 'maxHeight' => '150px'],
                ['type' => 'text', 'name' => 'title', 'label' => 'Title', 'translated' => true, 'rules' => 'required', 'ext' => 'update:slugs:slugSourceValue:modelValue'],
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
                            'label' => 'Session Title', 'type' => 'text',
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
            ],
        ],
    ],
];
