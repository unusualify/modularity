<?php

namespace Modules\Cms\Services\Stylesheet;

/**
 * Generates deterministic utility rules from {@code definition.utilities}.
 *
 * Patterns:
 * - {@code spacing}: scale + allowed {@code properties} ({@code padding|margin|gap})
 * - Most layout/typography: {@code scale} maps class token → CSS value (validated)
 * - {@code flex.presets}, {@code boxShadow.presets}, etc.: preset name → sanitized declaration lists
 *
 * Colour and font-stack inputs are sanitized; arbitrary {@code @} rules / {@code expression()} etc. are rejected.
 *
 * Tipografi & metin:
 * {@code fontSize fontWeight lineHeight letterSpacing fontFamily}, {@code color backgroundColor caretColor accentColor}, {@code textDecorationColor opacity}
 * Metin kutusu/hizalama:
 * {@code textAlign textTransform textDecoration verticalAlign whiteSpace overflowWrap wordBreak hyphens writingMode}, {@code textOverflow}
 *
 * Kutu ölçüleri:
 * {@code width height minWidth maxWidth minHeight maxHeight}, {@code borderRadius borderWidth}, köşelik kenarları (border*Radius), yan border genişlikleri
 *
 * Görünürlük & taşma:
 * {@code display visibility overflow overflowX overflowY}, {@code overscrollBehavior overscrollBehaviorX overscrollBehaviorY}
 *
 * Konumlanma & üst katmanlar:
 * {@code position inset insetTop insetRight insetBottom insetLeft zIndex}
 *
 * Flex / Grid:
 * {@code flexPreset} özel bloklar (aynı yapı ile); {@code flexFlow} blokları {@code presets};
 * {@code justifyContent justifyItems justifySelf alignItems alignContent alignSelf flexDirection flexWrap gapRowGap gapColumnGap}
 * {@code placeContent placeItems placeSelf gridAutoFlow}
 *
 * Gölgeler/çizgiler/renk:
 * {@code boxShadow outline textShadow} presets; {@code outlineWidth outlineOffset outlineStyle outlineColor}
 * Kenarlık stilleri/rengi: {@code borderStyle borderColor borderTopColor …}
 *
 * Medya/UI:
 * {@code objectFit aspectRatio resize cursor pointerEvents isolation mixBlendMode userSelect appearance touchAction}
 *
 * Geçiş/animasyon yüzleri:
 * {@code transitionDuration transitionTimingFunction transitionDelay}; {@code transitionProperty backdropFilter filter} presets
 *
 * Scroll:
 * {@code scrollBehavior scrollSnapAlign scrollSnapStop scrollSnapType} (snap type presets ile)
 *
 * Liste çok geniş olduğundan sadece ihtiyaç duyulan anahtarları JSON’a eklemeniz yeterlidir.
 *
 * Sınıf öneki: varsayılan {@code u-} — {@code definition.utilities.class_prefix} veya uygulama
 * {@code modularous.cms_stylesheets.utilities_class_prefix} ile değiştirilebilir.
 *
 * Şema uyumluluğu için eski isimleri de koruruz:
 * - {@code flex} + {@code presets} → ile flex blokları üretilir
 * - {@code flexFlow} için ayrıca {@code flexFlow.presets} kullanın (çakışma yok).
 */
final class UtilityCssGenerator
{
    /** @var list<string> */
    private const ALLOWED_SPACING_PROPS = ['padding', 'margin', 'gap'];

    /** @var list<string> */
    private const FLEX_JUSTIFY = [
        'flex-start', 'flex-end', 'center', 'space-between', 'space-around', 'space-evenly', 'stretch', 'normal', 'start', 'end',
    ];

    /** @var list<string> */
    private const FLEX_ALIGN = [
        'stretch', 'flex-start', 'flex-end', 'center', 'baseline', 'normal', 'start', 'end', 'self-start', 'self-end',
        'legacy', 'legacy center', 'auto',
    ];

    /** @var list<string> */
    private const FLEX_DIR = ['row', 'row-reverse', 'column', 'column-reverse'];

    /** @var list<string> */
    private const FLEX_WRAP = ['nowrap', 'wrap', 'wrap-reverse'];

    /** @var list<string> */
    private const ALIGN_CONTENT_VALUES = [
        'stretch', 'flex-start', 'flex-end', 'center', 'space-between', 'space-around', 'space-evenly',
        'start', 'end', 'baseline',
    ];

    /** @var list<string> */
    private const POINTER_EVENTS_VALUES = [
        'auto', 'none', 'visiblePainted', 'visibleFill', 'visibleStroke', 'visible', 'painted',
        'fill', 'stroke', 'all',
    ];

    /** @var list<string> */
    private const ISOLATION_VALUES = ['auto', 'isolate'];

    /** @var list<string> */
    private const MIX_BLEND_VALUES = [
        'normal', 'multiply', 'screen', 'overlay', 'darken', 'lighten', 'color-dodge', 'color-burn',
        'hard-light', 'soft-light', 'difference', 'exclusion', 'hue', 'saturation', 'color', 'luminosity',
    ];

    /**
     * Global namespace for generated utility class names (default {@code u-}).
     * Override with {@code definition.utilities.class_prefix} or legacy {@code utility_class_prefix};
     * app default: {@code modularous.cms_stylesheets.utilities_class_prefix}.
     */
    private string $utilityNamespacePrefix = 'u-';

    /**
     * @param  string  $suffix  Portion after the namespace (e.g. {@code w-} → {@code u-w-} when namespace is {@code u-}).
     */
    private function utilP(string $suffix): string
    {
        return $this->utilityNamespacePrefix . $suffix;
    }

    private function setUtilityNamespaceFromUtilities(array $utilities): void
    {
        $this->utilityNamespacePrefix = $this->resolveUtilityNamespacePrefix($utilities);
    }

    /**
     * @param  array<string, mixed>  $utilities
     * @return array<string, mixed>
     */
    private function utilitiesWithoutMetaKeys(array $utilities): array
    {
        unset($utilities['class_prefix'], $utilities['utility_class_prefix']);

        return $utilities;
    }

    /**
     * @param  array<string, mixed>  $utilities
     */
    private function resolveUtilityNamespacePrefix(array $utilities): string
    {
        if (array_key_exists('class_prefix', $utilities)) {
            $raw = $utilities['class_prefix'];

            return $this->normalizeUtilityNamespacePrefix(is_string($raw) ? $raw : null, true);
        }

        if (array_key_exists('utility_class_prefix', $utilities)) {
            $raw = $utilities['utility_class_prefix'];

            return $this->normalizeUtilityNamespacePrefix(is_string($raw) ? $raw : null, true);
        }

        $fromConfig = modularousConfig('cms_stylesheets.utilities_class_prefix', 'u-');

        return $this->normalizeUtilityNamespacePrefix(is_string($fromConfig) ? $fromConfig : null, false);
    }

    private function normalizeUtilityNamespacePrefix(?string $raw, bool $allowEmptyFromDefinition): string
    {
        if ($raw === null) {
            return $allowEmptyFromDefinition ? '' : 'u-';
        }
        $s = trim($raw);
        if ($s === '') {
            return $allowEmptyFromDefinition ? '' : 'u-';
        }
        $t = preg_replace('/[^a-zA-Z0-9_-]/', '', $s) ?? '';
        if ($t === '' || ! preg_match('/^[a-zA-Z_]/', $t)) {
            return 'u-';
        }
        if (! str_ends_with($t, '-')) {
            $t .= '-';
        }

        return $t;
    }

    /**
     * @param  array<string, mixed>  $utilities
     */
    public function generate(array $utilities): string
    {
        $this->setUtilityNamespaceFromUtilities($utilities);
        $utilities = $this->utilitiesWithoutMetaKeys($utilities);
        if ($utilities === []) {
            return '';
        }

        $chunks = [];

        $push = static function (?string $css, array &$into): void {
            $css = $css !== null ? trim($css) : '';
            if ($css !== '') {
                $into[] = $css;
            }
        };

        $push(isset($utilities['spacing']) && is_array($utilities['spacing']) ? $this->spacing($utilities['spacing']) : '', $chunks);
        $flexBlock = isset($utilities['flex']) && is_array($utilities['flex']) ? $utilities['flex'] : null;
        if ($flexBlock !== null && isset($flexBlock['presets']) && is_array($flexBlock['presets'])) {
            $push($this->declarationPresets($flexBlock['presets'], $flexBlock, $this->utilP('flex-'), 'preset'), $chunks);
        }
        $push(isset($utilities['flexFlow']) && is_array($utilities['flexFlow']) ? $this->declarationPresets(
            isset($utilities['flexFlow']['presets']) && is_array($utilities['flexFlow']['presets']) ? $utilities['flexFlow']['presets'] : [],
            $utilities['flexFlow'],
            $this->utilP('fflow-'),
            'preset'
        ) : '', $chunks);

        $push(isset($utilities['fontSize']) && is_array($utilities['fontSize']) ? $this->fontSizes($utilities['fontSize']) : '', $chunks);

        foreach ([
            ['w', $utilities['width'] ?? null, 'width', $this->utilP('w-')],
            ['h', $utilities['height'] ?? null, 'height', $this->utilP('h-')],
            ['min-w', $utilities['minWidth'] ?? null, 'min-width', $this->utilP('min-w-')],
            ['max-w', $utilities['maxWidth'] ?? null, 'max-width', $this->utilP('max-w-')],
            ['min-h', $utilities['minHeight'] ?? null, 'min-height', $this->utilP('min-h-')],
            ['max-h', $utilities['maxHeight'] ?? null, 'max-height', $this->utilP('max-h-')],
        ] as [, $cfg, $prop, $px]) {
            $push(is_array($cfg) ? $this->scaleCssProperty($cfg, $prop, $px, false) : '', $chunks);
        }

        $push(is_array($utilities['fontWeight'] ?? null) ? $this->fontWeight($utilities['fontWeight']) : '', $chunks);
        $push(is_array($utilities['lineHeight'] ?? null) ? $this->lineHeightScale($utilities['lineHeight']) : '', $chunks);
        $push(is_array($utilities['letterSpacing'] ?? null) ? $this->scaleCssProperty($utilities['letterSpacing'], 'letter-spacing', $this->utilP('tracking-'), false) : '', $chunks);
        $push(is_array($utilities['fontFamily'] ?? null) ? $this->fontFamilyScale($utilities['fontFamily']) : '', $chunks);
        $push(is_array($utilities['opacity'] ?? null) ? $this->opacityScale($utilities['opacity']) : '', $chunks);

        $push(is_array($utilities['color'] ?? null) ? $this->colorScale($utilities['color'], 'color', $this->utilP('text-')) : '', $chunks);
        $push(is_array($utilities['backgroundColor'] ?? null) ? $this->colorScale($utilities['backgroundColor'], 'background-color', $this->utilP('bg-')) : '', $chunks);
        $push(is_array($utilities['caretColor'] ?? null) ? $this->colorScale($utilities['caretColor'], 'caret-color', $this->utilP('caret-')) : '', $chunks);
        $push(is_array($utilities['accentColor'] ?? null) ? $this->colorScale($utilities['accentColor'], 'accent-color', $this->utilP('accent-')) : '', $chunks);

        foreach ([
            ['textDecorationColor', 'text-decoration-color', $this->utilP('underline-')],
            ['borderTopColor', 'border-top-color', $this->utilP('btc-')],
            ['borderRightColor', 'border-right-color', $this->utilP('brc-')],
            ['borderBottomColor', 'border-bottom-color', $this->utilP('bbc-')],
            ['borderLeftColor', 'border-left-color', $this->utilP('blc-')],
            ['borderColor', 'border-color', $this->utilP('bc-')],
            ['outlineColor', 'outline-color', $this->utilP('oc-')],
        ] as [$k, $prop, $px]) {
            $push(is_array($utilities[$k] ?? null) ? $this->colorScale($utilities[$k], $prop, $px) : '', $chunks);
        }

        foreach ([
            ['borderRadius', 'border-radius', $this->utilP('rounded-')],
            ['borderWidth', 'border-width', $this->utilP('border-')],
            ['outlineWidth', 'outline-width', $this->utilP('ring-w-')],
            ['outlineOffset', 'outline-offset', $this->utilP('ring-offset-')],
            ['borderTopLeftRadius', 'border-top-left-radius', $this->utilP('rounded-tl-')],
            ['borderTopRightRadius', 'border-top-right-radius', $this->utilP('rounded-tr-')],
            ['borderBottomLeftRadius', 'border-bottom-left-radius', $this->utilP('rounded-bl-')],
            ['borderBottomRightRadius', 'border-bottom-right-radius', $this->utilP('rounded-br-')],
            ['borderTopWidth', 'border-top-width', $this->utilP('border-t-')],
            ['borderRightWidth', 'border-right-width', $this->utilP('border-r-')],
            ['borderBottomWidth', 'border-bottom-width', $this->utilP('border-b-')],
            ['borderLeftWidth', 'border-left-width', $this->utilP('border-l-')],
            ['transitionDuration', 'transition-duration', $this->utilP('duration-')],
            ['transitionDelay', 'transition-delay', $this->utilP('delay-')],
            ['flexBasis', 'flex-basis', $this->utilP('fb-')],
            ['insetTop', 'top', $this->utilP('top-')],
            ['insetRight', 'right', $this->utilP('right-')],
            ['insetBottom', 'bottom', $this->utilP('bottom-')],
            ['insetLeft', 'left', $this->utilP('left-')],
        ] as [$k, $prop, $px]) {
            $allowBasis = ($k === 'flexBasis');
            $push(is_array($utilities[$k] ?? null) ? $this->scaleCssProperty($utilities[$k], $prop, $px, $allowBasis) : '', $chunks);
        }

        foreach ([
            ['boxShadow', $this->utilP('shadow-')],
            ['textShadow', $this->utilP('ts-')],
            ['backdropFilter', $this->utilP('bd-')],
            ['filter', $this->utilP('filter-')],
            ['transitionProperty', $this->utilP('tp-')],
        ] as [$k, $px]) {
            $block = $utilities[$k] ?? null;
            if (! is_array($block) || ! isset($block['presets']) || ! is_array($block['presets'])) {
                continue;
            }
            $push($this->declarationPresets($block['presets'], $block, $px, 'preset'), $chunks);
        }

        $outlineBlock = $utilities['outline'] ?? null;
        if (is_array($outlineBlock) && isset($outlineBlock['presets']) && is_array($outlineBlock['presets'])) {
            $push($this->declarationPresets($outlineBlock['presets'], $outlineBlock, $this->utilP('outline-'), 'preset'), $chunks);
        }

        $snapType = $utilities['scrollSnapType'] ?? null;
        if (is_array($snapType) && isset($snapType['presets']) && is_array($snapType['presets'])) {
            $push($this->declarationPresets($snapType['presets'], $snapType, $this->utilP('snap-type-'), 'preset'), $chunks);
        }

        $push(is_array($utilities['aspectRatio'] ?? null) ? $this->aspectRatioScale($utilities['aspectRatio']) : '', $chunks);

        foreach ([
            ['textAlign', 'text-align', ['left', 'right', 'center', 'justify', 'start', 'end', 'match-parent'], $this->utilP('text-')],
            ['textTransform', 'text-transform', ['none', 'capitalize', 'uppercase', 'lowercase', 'full-width', 'full-size-kana'], $this->utilP('tt-')],
            ['textDecoration', 'text-decoration', ['none', 'underline', 'overline', 'line-through', 'blink'], $this->utilP('td-')],
            ['verticalAlign', 'vertical-align', ['baseline', 'sub', 'super', 'text-top', 'text-bottom', 'middle', 'top', 'bottom'], $this->utilP('va-')],
            ['whiteSpace', 'white-space', ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line', 'break-spaces'], $this->utilP('ws-')],
            ['overflow', 'overflow', ['visible', 'hidden', 'scroll', 'auto', 'clip'], $this->utilP('overflow-')],
            ['overflowX', 'overflow-x', ['visible', 'hidden', 'scroll', 'auto', 'clip'], $this->utilP('overflow-x-')],
            ['overflowY', 'overflow-y', ['visible', 'hidden', 'scroll', 'auto', 'clip'], $this->utilP('overflow-y-')],
            ['textOverflow', 'text-overflow', ['clip', 'ellipsis'], $this->utilP('to-')],
            ['wordBreak', 'word-break', ['normal', 'break-all', 'keep-all', 'break-word'], $this->utilP('break-')],
            ['overflowWrap', 'overflow-wrap', ['normal', 'break-word', 'anywhere'], $this->utilP('wrap-')],
            ['hyphens', 'hyphens', ['none', 'manual', 'auto'], $this->utilP('hyphens-')],
            ['writingMode', 'writing-mode', ['horizontal-tb', 'vertical-rl', 'vertical-lr'], $this->utilP('writing-')],
            ['transitionTimingFunction', 'transition-timing-function', ['linear', 'ease', 'ease-in', 'ease-out', 'ease-in-out', 'step-start', 'step-end'], $this->utilP('ease-')],
            ['display', 'display', ['none', 'block', 'inline', 'inline-block', 'contents', 'flow-root', 'list-item', 'flex', 'inline-flex', 'grid', 'inline-grid', 'table', 'inline-table', 'table-row', 'table-cell', 'table-row-group', 'table-header-group', 'table-footer-group', 'table-column', 'table-column-group', 'run-in', 'ruby', 'ruby-text', 'ruby-base'], $this->utilP('d-')],
            ['visibility', 'visibility', ['visible', 'hidden', 'collapse'], $this->utilP('vis-')],
            ['outlineStyle', 'outline-style', ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset', 'hidden', 'auto'], $this->utilP('os-type-')],
            ['borderStyle', 'border-style', ['none', 'solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset', 'hidden'], $this->utilP('bstyle-')],
            ['resize', 'resize', ['none', 'both', 'horizontal', 'vertical'], $this->utilP('resize-')],
            ['appearance', 'appearance', ['none', 'auto'], $this->utilP('appearance-')],
            ['touchAction', 'touch-action', ['auto', 'none', 'manipulation', 'pan-x', 'pan-left', 'pan-right', 'pan-y', 'pinch-zoom'], $this->utilP('touch-')],
            ['cursor', 'cursor', ['auto', 'default', 'pointer', 'wait', 'progress', 'text', 'help', 'not-allowed', 'grab', 'grabbing', 'move', 'nwse-resize', 'nesw-resize', 'ew-resize', 'ns-resize', 'crosshair', 'alias', 'cell', 'zoom-in', 'zoom-out', 'context-menu'], $this->utilP('cursor-')],
            ['pointerEvents', 'pointer-events', self::POINTER_EVENTS_VALUES, $this->utilP('pe-')],
            ['isolate', 'isolation', self::ISOLATION_VALUES, $this->utilP('iso-')],
            ['mixBlendMode', 'mix-blend-mode', self::MIX_BLEND_VALUES, $this->utilP('blend-')],
            ['objectFit', 'object-fit', ['fill', 'contain', 'cover', 'none', 'scale-down'], $this->utilP('fit-')],
            ['overscrollBehavior', 'overscroll-behavior', ['auto', 'contain', 'none'], $this->utilP('os-')],
            ['overscrollBehaviorX', 'overscroll-behavior-x', ['auto', 'contain', 'none'], $this->utilP('os-x-')],
            ['overscrollBehaviorY', 'overscroll-behavior-y', ['auto', 'contain', 'none'], $this->utilP('os-y-')],
            ['userSelect', 'user-select', ['none', 'auto', 'text', 'all', 'contain'], $this->utilP('select-')],
            ['scrollBehavior', 'scroll-behavior', ['auto', 'smooth'], $this->utilP('scroll-b-')],
            ['scrollSnapAlign', 'scroll-snap-align', ['none', 'start', 'end', 'center'], $this->utilP('snap-')],
            ['scrollSnapStop', 'scroll-snap-stop', ['normal', 'always'], $this->utilP('snap-stop-')],
            ['gridAutoFlow', 'grid-auto-flow', ['row', 'column', 'dense', 'row dense', 'column dense'], $this->utilP('grid-flow-')],
            ['justifyContent', 'justify-content', self::FLEX_JUSTIFY, $this->utilP('jc-')],
            ['justifyItems', 'justify-items', array_merge(self::FLEX_ALIGN, ['stretch']), $this->utilP('ji-')],
            ['justifySelf', 'justify-self', array_merge(self::FLEX_ALIGN, ['auto', 'stretch', 'normal']), $this->utilP('js-')],
            ['alignItems', 'align-items', self::FLEX_ALIGN, $this->utilP('items-')],
            ['alignContent', 'align-content', self::ALIGN_CONTENT_VALUES, $this->utilP('ac-')],
            ['alignSelf', 'align-self', array_merge(self::FLEX_ALIGN, ['auto']), $this->utilP('self-')],
            ['flexDirection', 'flex-direction', self::FLEX_DIR, $this->utilP('fd-')],
            ['flexWrap', 'flex-wrap', self::FLEX_WRAP, $this->utilP('fw-')],
            ['position', 'position', ['static', 'relative', 'absolute', 'fixed', 'sticky'], $this->utilP('pos-')],
        ] as [$key, $cssProp, $whitelist, $defPrefix]) {
            $blk = $utilities[$key] ?? null;
            if (! is_array($blk)) {
                continue;
            }
            $push($this->enumerationFromConfig($blk, $cssProp, $whitelist, $defPrefix), $chunks);
        }

        $justifyPlace = [...self::FLEX_JUSTIFY, ...self::ALIGN_CONTENT_VALUES, 'baseline', 'inherit', 'initial', 'unset'];
        $push(is_array($utilities['placeContent'] ?? null) ? $this->enumerationFromConfig($utilities['placeContent'], 'place-content', $justifyPlace, $this->utilP('place-content-')) : '', $chunks);
        $push(is_array($utilities['placeItems'] ?? null) ? $this->enumerationFromConfig($utilities['placeItems'], 'place-items', array_merge(self::FLEX_ALIGN, ['stretch', 'normal', 'inherit', 'initial', 'unset']), $this->utilP('place-items-')) : '', $chunks);
        $push(is_array($utilities['placeSelf'] ?? null) ? $this->enumerationFromConfig($utilities['placeSelf'], 'place-self', array_merge(self::FLEX_ALIGN, ['auto', 'stretch', 'normal', 'inherit', 'initial', 'unset']), $this->utilP('place-self-')) : '', $chunks);

        $push(is_array($utilities['inset'] ?? null) ? $this->insetUnified($utilities['inset']) : '', $chunks);
        $push(is_array($utilities['gapColumnGap'] ?? null) ? $this->gapAxis($utilities['gapColumnGap'], 'column-gap', $this->utilP('gap-x-'), 'column-gap') : '', $chunks);
        $push(is_array($utilities['gapRowGap'] ?? null) ? $this->gapAxis($utilities['gapRowGap'], 'row-gap', $this->utilP('gap-y-'), 'row-gap') : '', $chunks);
        /** @deprecated use {@code gapRowGap}/{@code gapColumnGap} instead */
        $push(is_array($utilities['gapRowColumn'] ?? null) ? $this->gapRowColumnLegacy($utilities['gapRowColumn']) : '', $chunks);

        $push(is_array($utilities['flexGrow'] ?? null) ? $this->flexGrowShrink($utilities['flexGrow'], 'flex-grow', $this->utilP('grow-'), 0, 12) : '', $chunks);
        $push(is_array($utilities['flexShrink'] ?? null) ? $this->flexGrowShrink($utilities['flexShrink'], 'flex-shrink', $this->utilP('shrink-'), 0, 12) : '', $chunks);
        $push(is_array($utilities['flexOrder'] ?? null) ? $this->ordinalScaleUtility($utilities['flexOrder'], 'order', $this->utilP('order-'), true, false, null, null) : '', $chunks);
        $push(is_array($utilities['zIndex'] ?? null) ? $this->ordinalScaleUtility($utilities['zIndex'], 'z-index', $this->utilP('z-'), true, false, -99999, 99999) : '', $chunks);

        return implode("\n", array_filter($chunks));
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function spacing(array $cfg): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilityNamespacePrefix;
        $props = isset($cfg['properties']) && is_array($cfg['properties']) ? $cfg['properties'] : ['padding'];

        $props = array_values(array_filter($props, fn ($p) => is_string($p) && in_array($p, self::ALLOWED_SPACING_PROPS, true)));
        if ($props === [] || $scale === []) {
            return '';
        }

        $out = [];
        foreach ($scale as $token => $raw) {
            if ((! is_string($token) && ! is_int($token)) || ! is_string($raw)) {
                continue;
            }
            $token = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $token) ?? '';
            if ($token === '') {
                continue;
            }
            $value = $this->safeCssLengthOrKeyword($raw);
            if ($value === null) {
                continue;
            }

            foreach ($props as $prop) {
                $class = $this->safeClassSegment($prefix . $this->abbr($prop) . '-' . $token);
                if ($class === null) {
                    continue;
                }
                $out[] = sprintf(".%s{%s:%s;}", $class, $prop, $value);
            }
        }

        return implode("\n", $out);
    }

    private function abbr(string $prop): string
    {
        return match ($prop) {
            'padding' => 'p',
            'margin' => 'm',
            'gap' => 'gap',
            default => 'x',
        };
    }

    /**
     * Declaration presets ({@code name} => declarations string).
     *
     * {@code presetKey}: config key hosting the map (normally {@code presets}); passed through for callers, unused here.
     *
     * @param  array<string, string|array<mixed>|object>  $presetsMap
     * @param  array<string, mixed>  $wholeBlock
     */
    private function declarationPresets(array $presetsMap, array $wholeBlock, string $defaultPrefix, string $_presetKey): string
    {
        unset($_presetKey);
        $prefix = isset($wholeBlock['prefix']) && is_string($wholeBlock['prefix']) ? $wholeBlock['prefix'] : $defaultPrefix;
        if ($presetsMap === []) {
            return '';
        }

        $out = [];
        foreach ($presetsMap as $name => $declarationList) {
            if (! is_string($name)) {
                continue;
            }
            if (! is_string($declarationList)) {
                continue;
            }
            $name = preg_replace('/[^a-zA-Z0-9_-]/', '', $name) ?? '';
            if ($name === '') {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $name);
            if ($class === null) {
                continue;
            }
            $sanitized = $this->sanitizeInlineDeclarations($declarationList);
            if ($sanitized === '') {
                continue;
            }
            $out[] = '.' . $class . '{' . $sanitized . '}';
        }

        unset($presetKey);

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function fontSizes(array $cfg): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('text-');
        if ($scale === []) {
            return '';
        }

        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || ! is_string($raw)) {
                continue;
            }
            $token = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($token === '') {
                continue;
            }
            $value = $this->safeCssLengthOrKeyword($raw);
            if ($value === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $token);
            if ($class === null) {
                continue;
            }
            $out[] = sprintf('.%s{font-size:%s;}', $class, $value);
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function scaleCssProperty(array $cfg, string $cssProperty, string $defaultPrefix, bool $allowBasisKeywords): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $defaultPrefix;
        if ($scale === []) {
            return '';
        }

        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || (! is_string($raw) && ! is_numeric($raw))) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($tokenClean === '') {
                continue;
            }
            $value = $allowBasisKeywords ? $this->sanitizeFlexBasis((string) $raw) : $this->sanitizeBoxLength((string) $raw);
            if ($value === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class === null) {
                continue;
            }
            $out[] = sprintf('.%s{%s:%s;}', $class, $cssProperty, $value);
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>|null  $cfg
     */
    private function fontWeight(?array $cfg): string
    {
        if ($cfg === null || $cfg === []) {
            return '';
        }

        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('fw-');
        if ($scale === []) {
            return '';
        }

        $whitelist = array_merge(
            ['normal', 'bold', 'bolder', 'lighter'],
            array_map(static fn (int $i): string => (string) ($i * 100), range(1, 9))
        );

        return $this->enumerationFromWhitelistScale(['scale' => $scale, 'prefix' => $prefix], $whitelist, 'font-weight');
    }

    /**
     * @param  array<string, mixed>|null  $cfg
     */
    private function lineHeightScale(?array $cfg): string
    {
        if ($cfg === null || $cfg === []) {
            return '';
        }

        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('leading-');
        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || ! is_string($raw)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($tokenClean === '') {
                continue;
            }
            $v = trim($raw);
            if ($v !== '' && preg_match('/^-?(?:\\d+\\.\\d+|\\d+)$/u', $v)) {
                $value = $v;
            } else {
                $value = $this->safeCssLengthOrKeyword($v);
                if ($value === null || in_array($value, ['normal', 'inherit', 'unset', 'initial'], true)) {
                    $value = in_array($v, ['normal', 'inherit', 'unset', 'initial'], true) ? $v : ($this->safeCssLengthOrKeyword($v) ?? null);
                }
            }
            if ($value === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{line-height:%s;}', $class, $value);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>|null  $cfg
     */
    private function fontFamilyScale(?array $cfg): string
    {
        if ($cfg === null || $cfg === []) {
            return '';
        }

        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('font-');
        $out = [];
        foreach ($scale as $token => $stack) {
            if (! is_string($token) || ! is_string($stack)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            $safeStack = $this->sanitizeFontFamilyStack($stack);
            if ($tokenClean === '' || $safeStack === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{font-family:%s;}', $class, $safeStack);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>|null  $cfg
     */
    private function opacityScale(?array $cfg): string
    {
        if ($cfg === null || $cfg === []) {
            return '';
        }

        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('opacity-');
        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || (! is_string($raw) && ! is_numeric($raw))) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            $s = trim((string) $raw);
            if ($tokenClean === '' || $s === '') {
                continue;
            }

            $value = null;
            if ($s[strlen($s) - 1] === '%' && preg_match('/^\d+(?:\.\d+)?%$/', $s)) {
                $n = (float) $s;
                if ($n >= 0 && $n <= 100) {
                    $value = ($n / 100);
                }
            } elseif (preg_match('/^-?(?:\\d+|\\d*\.\d+)$/', $s)) {
                $f = (float) $s;
                if ($f >= 0 && $f <= 1) {
                    $value = $f;
                }
            }

            if ($value === null) {
                continue;
            }
            $css = rtrim(rtrim(sprintf('%.4F', $value), '0'), '.');
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{opacity:%s;}', $class, $css);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function colorScale(array $cfg, string $cssProp, string $defaultPrefix): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $defaultPrefix;
        if ($scale === []) {
            return '';
        }

        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || ! is_string($raw)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            $c = $this->safeCssColor($raw);
            if ($tokenClean === '' || $c === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{%s:%s;}', $class, $cssProp, $c);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>|null  $cfg
     */
    private function aspectRatioScale(?array $cfg): string
    {
        if ($cfg === null || $cfg === []) {
            return '';
        }

        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('ar-');

        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || ! is_string($raw)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            $s = trim($raw);
            if ($tokenClean === '') {
                continue;
            }

            // Accept "auto", "16/9", or a single number (width of ratio 1:n)
            $value = null;
            if ($s === 'auto') {
                $value = 'auto';
            } elseif (preg_match('#^(\d+(?:\.\d+)?)\s*/\s*(\d+(?:\.\d+)?)$#', $s, $m)) {
                $value = $m[1] . '/' . $m[2];
            } elseif (preg_match('/^\d+(?:\.\d+)?$/', $s)) {
                $value = $s;
            }
            if ($value === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{aspect-ratio:%s;}', $class, $value);
            }
        }

        return implode("\n", $out);
    }

    /** @deprecated */
    /**
     * @param  array<string, mixed>  $cfg
     */
    private function gapRowColumnLegacy(array $cfg): string
    {
        $row = isset($cfg['row']) && is_array($cfg['row']) ? $cfg['row'] : null;
        $column = isset($cfg['column']) && is_array($cfg['column']) ? $cfg['column'] : null;
        $parts = '';
        $parts .= $column !== null ? $this->gapAxis($column, 'column-gap', $this->utilP('gap-x-'), 'gapColumn') : '';
        $parts .= ($parts !== '' && $row !== null ? "\n" : '') . ($row !== null ? $this->gapAxis($row, 'row-gap', $this->utilP('gap-y-'), 'gapRow') : '');

        return trim($parts);
    }

    /**
     * Row / column gaps as length scales — separate from unified {@code spacing.gap}.
     *
     * @param  array<string, mixed>  $cfg
     */
    private function gapAxis(array $cfg, string $prop, string $defaultPrefix, string $debugIgnored): string
    {
        unset($debugIgnored);

        return $this->scaleCssProperty($cfg, $prop, $defaultPrefix, false);
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function insetUnified(array $cfg): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $prefix = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $this->utilP('inset-');
        if ($scale === []) {
            return '';
        }

        $out = [];
        foreach ($scale as $token => $raw) {
            if (! is_string($token) || (! is_string($raw) && ! is_numeric($raw))) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($tokenClean === '') {
                continue;
            }
            $value = $this->sanitizeBoxLength((string) $raw);
            if ($value === null) {
                continue;
            }
            $class = $this->safeClassSegment($prefix . $tokenClean);
            if ($class === null) {
                continue;
            }
            $out[] = sprintf('.%s{top:%s;right:%s;bottom:%s;left:%s;}', $class, $value, $value, $value, $value);
        }

        return implode("\n", $out);
    }

    /**
     * Unitless ordinal properties (flex-grow/shrink/order/z-index optionally bounded).
     *
     * @param  array<string, mixed>  $cfg
     */
    private function flexGrowShrink(array $cfg, string $cssProp, string $prefix, int $min, int $max): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $pfx = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $prefix;
        $out = [];

        foreach ($scale as $token => $raw) {
            if (! is_string($token) || (! is_numeric($raw) && ! preg_match('/^\d+$/', (string) $raw))) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($tokenClean === '') {
                continue;
            }
            $i = is_int($raw) ? $raw : (int) $raw;
            if ($i < $min || $i > $max) {
                continue;
            }
            $class = $this->safeClassSegment($pfx . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{%s:%d;}', $class, $cssProp, $i);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>  $cfg
     */
    private function ordinalScaleUtility(
        array $cfg,
        string $cssProp,
        string $prefix,
        bool $allowLeadingUnitsFallback,
        bool $allowPxRemForZ,
        ?int $min,
        ?int $max,
    ): string {
        unset($allowPxRemForZ);
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $pfx = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $prefix;
        $out = [];

        foreach ($scale as $token => $raw) {
            if (! is_string($token)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            if ($tokenClean === '') {
                continue;
            }

            $cssVal = null;
            if (is_numeric($raw)) {
                $i = (int) $raw;
                if ($min !== null && $max !== null && ($i < $min || $i > $max)) {
                    continue;
                }
                $cssVal = (string) $i;
            } elseif ($allowLeadingUnitsFallback && is_string($raw)) {
                $s = trim($raw);
                if (preg_match('/^-?\d+(?:px|rem|em)$/', $s) || preg_match('/^-?\d+$/', $s)) {
                    $cssVal = $s;
                }
            }

            if ($cssVal === null) {
                continue;
            }

            $class = $this->safeClassSegment($pfx . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{%s:%s;}', $class, $cssProp, $cssVal);
            }
        }

        return implode("\n", $out);
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @param  string[]                $whitelist
     */
    private function enumerationFromConfig(array $cfg, string $cssProperty, array $whitelist, string $defaultPrefix): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $pfx = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : $defaultPrefix;

        return $this->enumerationFromWhitelistScale(['scale' => $scale, 'prefix' => $pfx], array_values(array_unique($whitelist)), $cssProperty);
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @param  string[]                $whitelist
     */
    private function enumerationFromWhitelistScale(array $cfg, array $whitelist, string $cssProperty): string
    {
        $scale = isset($cfg['scale']) && is_array($cfg['scale']) ? $cfg['scale'] : [];
        $pfx = isset($cfg['prefix']) && is_string($cfg['prefix']) ? $cfg['prefix'] : '';
        $set = [];
        foreach ($whitelist as $w) {
            $set[$w] = true;
        }

        $out = [];
        foreach ($scale as $token => $value) {
            if (! is_string($token)) {
                continue;
            }
            if (! is_string($value)) {
                continue;
            }
            $tokenClean = preg_replace('/[^a-zA-Z0-9_-]/', '', $token) ?? '';
            $v = trim($value);
            if ($tokenClean === '' || $v === '') {
                continue;
            }

            if (! isset($set[$v])) {
                continue;
            }

            $class = $this->safeClassSegment($pfx . $tokenClean);
            if ($class !== null) {
                $out[] = sprintf('.%s{%s:%s;}', $class, $cssProperty, $v);
            }
        }

        return implode("\n", $out);
    }

    private function sanitizeBoxLength(string $raw): ?string
    {
        return $this->safeCssLengthOrKeyword($raw) ?? $this->safeIntrinsicSizeKeyword(trim($raw));
    }

    private function safeIntrinsicSizeKeyword(string $v): ?string
    {
        static $sizes = ['auto', 'max-content', 'min-content', 'fit-content', 'inherit', 'initial', 'unset'];

        return in_array($v, $sizes, true) ? $v : null;
    }

    private function sanitizeFlexBasis(string $raw): ?string
    {
        $t = trim($raw);
        if ($t !== '' && str_starts_with($t, 'content')) {
            return $t;
        }

        return $this->sanitizeBoxLength($raw);
    }

    private function safeCssColor(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '' || strlen($raw) > 200) {
            return null;
        }

        $lower = strtolower($raw);
        if (in_array($lower, ['transparent', 'inherit', 'unset', 'initial'], true)) {
            return $lower;
        }
        if ($lower === 'currentcolor') {
            return 'currentColor';
        }

        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $raw)) {
            return $raw;
        }

        if (preg_match('/^rgba?\(/i', $raw) && str_ends_with($raw, ')') && strlen($raw) < 160) {
            return $raw;
        }
        if (preg_match('/^hsla?\(/i', $raw) && str_ends_with($raw, ')') && strlen($raw) < 200) {
            return $raw;
        }
        if (preg_match('/^var\(--[a-zA-Z0-9_-]+\)$/', $raw)) {
            return $raw;
        }

        return null;
    }

    private function sanitizeFontFamilyStack(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $stack = preg_replace('/\s+/', ' ', trim($raw));
        if ($stack === '' || strlen($stack) > 320) {
            return null;
        }
        if (preg_match('/[;<>{}\\\]/', $stack)) {
            return null;
        }

        return $stack !== '' ? $stack : null;
    }

    private function safeCssLengthOrKeyword(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if ($raw === '0' || preg_match('/^-?(?:\\d+\\.?\\d*|\\.\\d+)(?:px|rem|em|%|vh|vw|ch|ex)$/u', $raw)) {
            return $raw;
        }

        if (preg_match('/^[a-zA-Z][a-zA-Z0-9()%,._\s\-]*$/u', $raw) && strlen($raw) < 200) {
            return $raw;
        }

        return null;
    }

    private function sanitizeInlineDeclarations(?string $raw): string
    {
        if ($raw === null) {
            return '';
        }

        $raw = str_replace(["\n", "\r"], '', $raw);
        if (strlen($raw) > 4096) {
            return '';
        }

        if (preg_match('/[<>@]|expression\s*\(/i', $raw)) {
            return '';
        }

        $raw = preg_replace('/\/\*.*?\*\//s', '', $raw) ?? '';

        if (str_contains($raw, '{') || str_contains($raw, '}')) {
            return '';
        }

        $parts = array_map(trim(...), explode(';', $raw));
        $safe = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (! preg_match('/^[a-zA-Z\-]+\s*:\s*.+$/u', $part)) {
                continue;
            }
            $safe[] = $part;
        }

        return implode(';', $safe);
    }

    private function safeClassSegment(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        if (strlen($raw) > 191) {
            return null;
        }
        if (! preg_match('/^-?[_a-zA-Z][_a-zA-Z0-9-]*$/u', $raw)) {
            return null;
        }

        return $raw;
    }
}
