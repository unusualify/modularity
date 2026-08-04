<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Support;

/**
 * Catalog of default HTTP error pages + PageLayout seed payloads.
 */
final class ErrorPageDefaults
{
    /**
     * @return list<array{error_code: string, name: string, published: bool}>
     */
    public static function pages(): array
    {
        return [
            ['error_code' => '404', 'name' => 'Not Found (404)', 'published' => true],
            ['error_code' => '403', 'name' => 'Forbidden (403)', 'published' => true],
            ['error_code' => '500', 'name' => 'Server Error (500)', 'published' => true],
        ];
    }

    /**
     * @return list<string>
     */
    public static function errorCodes(): array
    {
        return array_column(self::pages(), 'error_code');
    }

    /**
     * Package builtin full document ({@code @extends} standalone + Vuetify content).
     */
    public static function builtinViewName(string $errorCode): string
    {
        return 'error_page::error_page.' . $errorCode;
    }

    public static function layoutBuilderBodyOverrideViewName(string $layoutSlug, string $errorCode): string
    {
        return 'cms.layout_builder.' . $layoutSlug . '.' . $errorCode;
    }

    /**
     * Resolve a LayoutBuilder slug from host config (never hardcodes an app theme).
     */
    public static function configuredLayoutSlug(): string
    {
        return trim((string) modularousConfig('cms_layout_builder.default_layout_slug', ''));
    }
}
