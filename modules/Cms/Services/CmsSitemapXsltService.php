<?php

namespace Modules\Cms\Services;

use DOMDocument;
use Illuminate\Support\Facades\Log;
use XSLTProcessor;

/**
 * Applies the package sitemap XSLT for human-readable HTML (panel dry-run).
 * Public browsers load the same file via {@see \Modules\Cms\Http\Controllers\Front\PublicSitemapXslController}.
 */
final class CmsSitemapXsltService
{
    public function stylesheetPath(): string
    {
        return dirname(__DIR__) . '/Resources/assets/sitemap.xsl';
    }

    public function stylesheetContents(): ?string
    {
        $path = $this->stylesheetPath();
        if (! is_readable($path)) {
            return null;
        }

        $raw = file_get_contents($path);

        return is_string($raw) && $raw !== '' ? $raw : null;
    }

    /**
     * Transform sitemap XML to HTML. Returns null when the xsl extension/file is unavailable.
     */
    public function transformXmlToHtml(string $xml): ?string
    {
        if ($xml === '') {
            return null;
        }

        if (! extension_loaded('xsl') || ! class_exists(XSLTProcessor::class, false)) {
            return null;
        }

        $xslText = $this->stylesheetContents();
        if ($xslText === null) {
            return null;
        }

        $prev = libxml_use_internal_errors(true);
        try {
            $xmlDoc = new DOMDocument;
            // Avoid !== true: some libxml builds return int(1) on success.
            if (! $xmlDoc->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT)) {
                $this->logLibxmlFailure('load_xml');

                return null;
            }

            $xslDoc = new DOMDocument;
            if (! $xslDoc->loadXML($xslText, LIBXML_NONET | LIBXML_COMPACT)) {
                $this->logLibxmlFailure('load_xsl');

                return null;
            }

            $proc = new XSLTProcessor;
            if (! $proc->importStylesheet($xslDoc)) {
                $this->logLibxmlFailure('import_stylesheet');

                return null;
            }

            $html = $proc->transformToXML($xmlDoc);
            if (! is_string($html) || $html === '') {
                $this->logLibxmlFailure('transform');

                return null;
            }

            return $html;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($prev);
        }
    }

    private function logLibxmlFailure(string $stage): void
    {
        $messages = array_map(
            static fn (\LibXMLError $e): string => trim($e->message),
            libxml_get_errors(),
        );

        Log::warning('CmsSitemapXsltService transform failed.', [
            'stage' => $stage,
            'xsl_loaded' => extension_loaded('xsl'),
            'errors' => $messages,
        ]);
    }
}
