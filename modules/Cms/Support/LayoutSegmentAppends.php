<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

/**
 * Normalized Blade fragment map for extending a layout shell (keys: {@code head}, {@code body}, {@code footer}).
 */
final class LayoutSegmentAppends
{
    /**
     * @param mixed $raw Request / JSON payloads.
     * @return array{head:string,body:string,footer:string}
     */
    public static function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            return ['head' => '', 'body' => '', 'footer' => ''];
        }

        return [
            'head' => trim((string) ($raw['head'] ?? '')),
            'body' => trim((string) ($raw['body'] ?? '')),
            'footer' => trim((string) ($raw['footer'] ?? '')),
        ];
    }
}
