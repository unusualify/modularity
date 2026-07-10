<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\BulkCsv;

use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Unusualify\Modularous\Contracts\CanBulkSheet;
use Unusualify\Modularous\Services\BulkCsv\BulkCsvImportOrchestrator;
use Unusualify\Modularous\Services\BulkCsv\BulkImportService;
use Unusualify\Modularous\Tests\TestCase;

class BulkImportServiceTest extends TestCase
{
    public function test_import_delegates_to_orchestrator(): void
    {
        $bulkSheet = Mockery::mock(CanBulkSheet::class);
        $orchestrator = Mockery::mock(BulkCsvImportOrchestrator::class);
        $orchestrator->shouldReceive('import')
            ->once()
            ->with('name,email', true, $bulkSheet, 'contacts')
            ->andReturn(['ok' => true, 'dry_run' => true, 'tool_key' => 'contacts', 'rows' => [], 'summary' => []]);

        $service = new BulkImportService($orchestrator);

        $result = $service->import('name,email', true, $bulkSheet, 'contacts');

        $this->assertTrue($result['ok']);
        $this->assertSame('contacts', $result['tool_key']);
    }

    public function test_stream_export_delegates_to_orchestrator(): void
    {
        $bulkSheet = Mockery::mock(CanBulkSheet::class);
        $response = new StreamedResponse;

        $orchestrator = Mockery::mock(BulkCsvImportOrchestrator::class);
        $orchestrator->shouldReceive('streamExport')
            ->once()
            ->with($bulkSheet, 'export.csv')
            ->andReturn($response);

        $service = new BulkImportService($orchestrator);

        $this->assertSame($response, $service->streamExport($bulkSheet, 'export.csv'));
    }
}
