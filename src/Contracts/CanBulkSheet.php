<?php

namespace Unusualify\Modularous\Contracts;

use Unusualify\Modularous\Http\Controllers\Traits\ManageBulkSheet;

/**
 * Panel controller contract: CSV bulk sheet UI/routes ({@see ManageBulkSheet})
 * plus import/export column schema, validation, persistence, and streaming export.
 *
 * Declarative sheet options may live in route config `bulk_sheet` (inline array or
 * Blueprint class `{Route}BulkSheet`). {@see ManageBulkSheet::bulkSheetRouteConfig()}.
 *
 * {@see ManageBulkSheet::bulkSheetToolKey()} defaults from module/route
 * names unless `tool_key` is set in the route `bulk_sheet` config.
 */
interface CanBulkSheet
{
    /**
     * Human-readable columns for admin “sheet” / tool UI and export headers.
     * Prefer `bulk_sheet.fields` from Blueprint/config when present.
     *
     * @return list<array{key: string, label: string, required?: bool, aliases?: list<string>}>
     */
    public function bulkSheetFields(): array;

    /**
     * Suggested filename for Content-Disposition on export.
     */
    public function bulkSheetExportDownloadFilename(): string;

    /**
     * @param list<array{line: int, values: array<string, string>}> $records
     * @return list<array<string, mixed>>
     */
    public function bulkSheetPrepareAndValidateRows(array $records): array;

    /**
     * @param list<array<string, mixed>> $prepared only valid rows
     * @return array{created: int, updated: int}
     */
    public function bulkSheetCommitPreparedRows(array $prepared): array;

    /**
     * Write UTF-8 CSV (header + rows) to an already-open write stream.
     *
     * @param resource $resource
     */
    public function bulkSheetStreamExport($resource): void;
}
