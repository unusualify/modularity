<?php

namespace Modules\Cms\Blueprint\StyleSheet\Form;

use Unusualify\Modularous\Contracts\ModuleRoute\ModuleRouteInputsProvider;
use Unusualify\Modularous\ModuleRoute;

final class StyleSheetFormInputs implements ModuleRouteInputsProvider
{
    /**
     * @return list<array<string, mixed>>
     */
    public function __invoke(ModuleRoute $route): array
    {
        return [
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
        ];
    }
}
