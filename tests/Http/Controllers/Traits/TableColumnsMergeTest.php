<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Http\Controllers\Traits;

use Unusualify\Modularous\Http\Controllers\Traits\Table\TableColumns;
use Unusualify\Modularous\Tests\TestCase;

class TableColumnsMergeTest extends TestCase
{
    public function test_merge_table_headers_before_actions_inserts_before_actions_column(): void
    {
        $stub = new class
        {
            use TableColumns;

            /**
             * @param array<int, mixed> $headers
             * @param array<int, mixed> $appended
             * @return array<int, mixed>
             */
            public function merge(array $headers, array $appended): array
            {
                return $this->mergeTableHeadersBeforeActions($headers, $appended);
            }
        };

        $merged = $stub->merge(
            [
                (object) ['key' => 'name'],
                (object) ['key' => 'actions'],
            ],
            [
                (object) ['key' => 'presentation_item_cache_formatted'],
            ],
        );

        $this->assertSame(
            ['name', 'presentation_item_cache_formatted', 'actions'],
            array_map(fn ($h) => $h->key, $merged),
        );
    }

    public function test_merge_table_headers_before_actions_appends_when_no_actions_column(): void
    {
        $stub = new class
        {
            use TableColumns;

            /**
             * @param array<int, mixed> $headers
             * @param array<int, mixed> $appended
             * @return array<int, mixed>
             */
            public function merge(array $headers, array $appended): array
            {
                return $this->mergeTableHeadersBeforeActions($headers, $appended);
            }
        };

        $merged = $stub->merge(
            [
                ['key' => 'name'],
            ],
            [
                ['key' => 'presentation_item_cache_formatted'],
            ],
        );

        $this->assertSame(
            ['name', 'presentation_item_cache_formatted'],
            array_map(fn ($h) => is_array($h) ? $h['key'] : $h->key, $merged),
        );
    }
}
