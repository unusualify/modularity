<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services\RemoteApi;

use Unusualify\Modularous\Services\RemoteApi\RemoteApiRecordDto;
use Unusualify\Modularous\Tests\TestCase;

class RemoteApiRecordDtoTest extends TestCase
{
    public function test_exposes_remote_id_and_array_payload(): void
    {
        $dto = new RemoteApiRecordDto(42, ['name' => 'Package']);

        $this->assertSame(42, $dto->getRemoteId());
        $this->assertSame([
            'id' => 42,
            'payload' => ['name' => 'Package'],
        ], $dto->toArray());
    }

    public function test_builds_from_api_row(): void
    {
        $dto = RemoteApiRecordDto::fromApiRow(['id' => 7, 'title' => 'Remote']);

        $this->assertSame(7, $dto->id);
        $this->assertSame('Remote', $dto->payload['title']);
    }
}
