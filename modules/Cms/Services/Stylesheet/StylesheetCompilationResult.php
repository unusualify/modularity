<?php

namespace Modules\Cms\Services\Stylesheet;

use Modules\Cms\Entities\StyleSheet;

/**
 * Result of compiling one {@see StyleSheet} record.
 *
 * - {@see $frameworkLinkHrefs} External stylesheets (Bootstrap/Tailwind CDN, vendor assets, or prelude links from definition).
 * - {@see $frameworkScriptSrcs} Optional framework scripts (e.g. Bootstrap bundle for components / transitions).
 * - {@see $inlineCss} Generated custom CSS (:root, utilities, raw, SCSS) persisted and served at the public bundle URL.
 */
final class StylesheetCompilationResult
{
    /**
     * @param list<string> $frameworkLinkHrefs
     * @param list<string> $frameworkScriptSrcs
     */
    public function __construct(
        public array $frameworkLinkHrefs,
        public string $inlineCss,
        public array $frameworkScriptSrcs = [],
    ) {}
}
