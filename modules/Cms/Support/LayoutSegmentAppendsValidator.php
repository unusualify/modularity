<?php

declare(strict_types=1);

namespace Modules\Cms\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Validates optional layout shell blade fragments ({@see LayoutSegmentAppends}) for panel composers and APIs.
 */
final class LayoutSegmentAppendsValidator
{
    /**
     * @return array{layout_segment_appends?: array<string, mixed>|null}
     */
    public static function validatedOrEmpty(Request $request): array
    {
        /** @var array<string, mixed> */
        return $request->validate([
            'layout_segment_appends' => 'nullable|array',
            'layout_segment_appends.head' => 'nullable|string',
            'layout_segment_appends.body' => 'nullable|string',
            'layout_segment_appends.footer' => 'nullable|string',
        ]);
    }

    /**
     * @return array{layout_segment_appends?: array<string, mixed>|null}
     */
    public static function validateRequest(Request $request): array
    {
        $validated = self::validatedOrEmpty($request);
        $normalized = LayoutSegmentAppends::normalize($validated['layout_segment_appends'] ?? null);
        self::enforceByteLimit($normalized);

        return $validated;
    }

    /**
     * @param  array{head:string,body:string,footer:string}  $normalized
     */
    public static function enforceByteLimit(array $normalized): void
    {
        $maxBytes = max(4096, (int) modularousConfig('cms_layout_builder.max_blade_segments_bytes', 512_000));
        if ($maxBytes > 0 && self::combinedBytes($normalized) > $maxBytes) {
        throw ValidationException::withMessages([
            'blade_segments' => [__('Combined layout blade segments exceed the configured byte limit.')],
        ]);
        }
    }

    /**
     * Raw UTF-8 byte length (excluding base layout bytes).
     *
     * @param  array{head:string,body:string,footer:string}  $normalized
     */
    public static function combinedBytes(array $normalized): int
    {
        return strlen($normalized['head'] . $normalized['body'] . $normalized['footer']);
    }
}
