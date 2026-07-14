<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\RemoteApi;

use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Services\RemoteApi\Contracts\RemoteApiConnectorInterface;
use Unusualify\Modularous\Services\RemoteApi\Exceptions\RemoteApiSyncException;

class RemoteApiSynchronizer
{
    public function __construct(
        private readonly RemoteApiAttributePartition $attributePartition = new RemoteApiAttributePartition,
    ) {}

    /**
     * @return array{
     *     configuration: array<string, mixed>,
     *     would_fetch: string,
     *     model: class-string<Model>,
     *     records: list<array{local_id: int|string, remote_id: int|string, action: string, label: string}>,
     *     without_remote_id: list<array{local_id: int|string, label: string}>,
     *     summary: array{local_total: int, would_update: int, without_remote_id: int}
     * }
     */
    public function previewSyncAll(RemoteApiConnectorInterface $connector, Repository $repository): array
    {
        $configuration = $connector->configuration();
        $model = $repository->getModel();
        $records = $model->newQuery()->with('remoteApiSource')->get();

        $linked = [];
        $withoutRemoteId = [];

        foreach ($records as $record) {
            $label = $this->resolveRecordLabel($record);
            $remoteId = method_exists($record, 'getRemoteApiId')
                ? $record->getRemoteApiId()
                : ($record->remoteApiSource?->{$configuration->remoteIdColumn()} ?? null);

            if ($remoteId === null) {
                $withoutRemoteId[] = [
                    'local_id' => $record->getKey(),
                    'label' => $label,
                ];

                continue;
            }

            $linked[] = [
                'local_id' => $record->getKey(),
                'remote_id' => $remoteId,
                'action' => 'update',
                'label' => $label,
            ];
        }

        return [
            'configuration' => $configuration->toSummaryArray(),
            'would_fetch' => sprintf('GET %s/%s (paginated list)', $configuration->baseUrl(), $configuration->endpoint()),
            'model' => $model::class,
            'records' => $linked,
            'without_remote_id' => $withoutRemoteId,
            'summary' => [
                'local_total' => $records->count(),
                'would_update' => count($linked),
                'without_remote_id' => count($withoutRemoteId),
            ],
        ];
    }

    /**
     * @return array{
     *     configuration: array<string, mixed>,
     *     remote_id: int|string,
     *     would_fetch: string,
     *     action: string,
     *     local_id: int|string|null,
     *     label: string|null
     * }
     */
    public function previewSyncRecord(
        RemoteApiConnectorInterface $connector,
        Repository $repository,
        int|string $remoteId,
    ): array {
        $configuration = $connector->configuration();
        $remoteIdColumn = $configuration->remoteIdColumn();
        $model = $repository->getModel();
        $existing = $model->newQuery()
            ->whereHas('remoteApiSource', fn ($query) => $query->where($remoteIdColumn, $remoteId))
            ->with('remoteApiSource')
            ->first();

        return [
            'configuration' => $configuration->toSummaryArray(),
            'remote_id' => $remoteId,
            'would_fetch' => sprintf('GET %s/%s', $configuration->baseUrl(), $configuration->showEndpoint($remoteId)),
            'action' => $existing ? 'update' : 'create',
            'local_id' => $existing?->getKey(),
            'label' => $existing ? $this->resolveRecordLabel($existing) : null,
        ];
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     skipped_records: list<array{remote_id: int|string, reason: string, message: string}>,
     *     http_requests: array{total: int, by_url: array<string, int>}
     * }
     */
    public function syncAll(RemoteApiConnectorInterface $connector, Repository $repository): array
    {
        $connector->resetRequestStats();

        if (! $connector->configuration()->importNewFromRemoteList()) {
            return $this->syncAllLinkedOnly($connector, $repository);
        }

        return $this->syncAllFromRemoteList($connector, $repository);
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     skipped_records: list<array{remote_id: int|string, reason: string, message: string}>,
     *     http_requests: array{total: int, by_url: array<string, int>}
     * }
     */
    private function syncAllLinkedOnly(RemoteApiConnectorInterface $connector, Repository $repository): array
    {
        $created = 0;
        $updated = 0;
        $skippedRecords = [];

        foreach ($this->linkedRemoteIds($connector, $repository) as $remoteId) {
            $row = $connector->fetchOne($remoteId);

            if ($row === null) {
                $skippedRecords[] = [
                    'remote_id' => $remoteId,
                    'reason' => 'not_in_remote_list',
                    'message' => sprintf(
                        'Linked remote record [%s] was not returned by the remote API; skipped (stale link or deleted remote record).',
                        $remoteId,
                    ),
                ];

                continue;
            }

            $result = $this->syncRecordFromRow($connector, $repository, $remoteId, $row);
            $result['created'] ? $created++ : $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($skippedRecords),
            'total' => $created + $updated,
            'skipped_records' => $skippedRecords,
            'http_requests' => $connector->flushRequestStats(),
        ];
    }

    /**
     * @return array{
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     skipped_records: list<array{remote_id: int|string, reason: string, message: string}>,
     *     http_requests: array{total: int, by_url: array<string, int>}
     * }
     */
    private function syncAllFromRemoteList(RemoteApiConnectorInterface $connector, Repository $repository): array
    {
        $created = 0;
        $updated = 0;
        $skippedRecords = [];
        $processedRemoteIds = [];
        $remoteRowsById = $this->indexRemoteList($connector);

        foreach ($this->linkedRemoteIds($connector, $repository) as $remoteId) {
            $row = $remoteRowsById[(string) $remoteId] ?? null;

            if ($row === null) {
                $skippedRecords[] = [
                    'remote_id' => $remoteId,
                    'reason' => 'not_in_remote_list',
                    'message' => sprintf(
                        'Linked remote record [%s] was not returned by the remote list; skipped (stale link or deleted remote record).',
                        $remoteId,
                    ),
                ];

                continue;
            }

            $result = $this->syncRecordFromRow($connector, $repository, $remoteId, $row);
            $processedRemoteIds[(string) $remoteId] = true;
            $result['created'] ? $created++ : $updated++;
        }

        foreach ($remoteRowsById as $remoteId => $row) {
            if (isset($processedRemoteIds[$remoteId])) {
                continue;
            }

            $result = $this->syncRecordFromRow($connector, $repository, $remoteId, $row);
            $result['created'] ? $created++ : $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => count($skippedRecords),
            'total' => $created + $updated,
            'skipped_records' => $skippedRecords,
            'http_requests' => $connector->flushRequestStats(),
        ];
    }

    /**
     * @return list<int|string>
     */
    private function linkedRemoteIds(RemoteApiConnectorInterface $connector, Repository $repository): array
    {
        $configuration = $connector->configuration();
        $remoteIdColumn = $configuration->remoteIdColumn();
        $remoteIds = [];
        $model = $repository->getModel();
        $keyName = $model->getKeyName();

        $model->newQuery()
            ->whereHas('remoteApiSource', function ($query) use ($remoteIdColumn) {
                $query->whereNotNull($remoteIdColumn)->where($remoteIdColumn, '!=', '');
            })
            ->select([$keyName])
            ->with(['remoteApiSource' => function ($query) use ($remoteIdColumn) {
                $query->select(['id', 'sourceable_id', 'sourceable_type', $remoteIdColumn]);
            }])
            ->chunkById(100, function ($records) use (&$remoteIds, $remoteIdColumn) {
                foreach ($records as $record) {
                    $remoteId = method_exists($record, 'getRemoteApiId')
                        ? $record->getRemoteApiId()
                        : ($record->remoteApiSource?->{$remoteIdColumn} ?? null);

                    if ($remoteId === null || $remoteId === '') {
                        continue;
                    }

                    $remoteIds[] = $remoteId;
                }
            });

        return $remoteIds;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function indexRemoteList(RemoteApiConnectorInterface $connector): array
    {
        $indexed = [];

        foreach ($connector->fetchList() as $row) {
            $remoteId = data_get($row, 'id');

            if ($remoteId === null || $remoteId === '') {
                continue;
            }

            $indexed[(string) $remoteId] = $row;
        }

        return $indexed;
    }

    /**
     * @return array{model: Model, created: bool}
     */
    public function syncRecord(
        RemoteApiConnectorInterface $connector,
        Repository $repository,
        int|string $remoteId,
    ): array {
        $row = $connector->fetchOne($remoteId);

        if ($row === null) {
            throw RemoteApiSyncException::recordNotFound($remoteId);
        }

        return $this->syncRecordFromRow($connector, $repository, $remoteId, $row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array{model: Model, created: bool}
     */
    public function syncRecordFromRow(
        RemoteApiConnectorInterface $connector,
        Repository $repository,
        int|string $remoteId,
        array $row,
    ): array {
        $configuration = $connector->configuration();
        $remoteIdColumn = $configuration->remoteIdColumn();
        $model = $repository->getModel();
        $existing = $model->newQuery()
            ->whereHas('remoteApiSource', fn ($query) => $query->where($remoteIdColumn, $remoteId))
            ->with('remoteApiSource')
            ->first();

        $existingAttributes = $existing
            ? array_merge($existing->getAttributes(), $existing->remoteApiSource?->toMergedAttributes() ?? [])
            : [];

        $mapped = $this->normalizeAttributes($connector->mapRow($row, $existingAttributes));
        $partition = $this->attributePartition->partition(
            $model,
            $configuration,
            $mapped,
            $existing?->getAttributes() ?? [],
        );

        if ($existing) {
            if (method_exists($existing, 'stripRemoteApiVirtualAttributes')) {
                $existing->stripRemoteApiVirtualAttributes();
            }

            if ($partition['local'] !== []) {
                $repository->update($existing->id, $partition['local']);
            }

            $this->syncRemoteApiSource($existing, $partition['remote']);

            return [
                'model' => $existing->fresh(['remoteApiSource']),
                'created' => false,
            ];
        }

        $created = $repository->create(
            $this->ensureRequiredParentAttributes($model, $partition['local'], $partition['remote'])
        );

        $this->syncRemoteApiSource($created, $partition['remote']);

        return [
            'model' => $created->fresh(['remoteApiSource']),
            'created' => true,
        ];
    }

    /**
     * @param array<string, mixed> $remoteAttributes
     */
    private function syncRemoteApiSource(Model $model, array $remoteAttributes): void
    {
        if (method_exists($model, 'stripRemoteApiVirtualAttributes')) {
            $model->stripRemoteApiVirtualAttributes();
        }

        if ($model->remoteApiSource) {
            $model->remoteApiSource->updateQuietly($remoteAttributes);

            return;
        }

        $model->remoteApiSource()->create($remoteAttributes);
    }

    /**
     * @param array<string, mixed> $local
     * @param array<string, mixed> $remote
     * @return array<string, mixed>
     */
    private function ensureRequiredParentAttributes(Model $model, array $local, array $remote): array
    {
        $fillable = array_flip($model->getFillable());

        if (! isset($local['name']) && isset($fillable['name'])) {
            $local['name'] = data_get($remote, 'synced_attributes.synced_name')
                ?? sprintf('Remote #%s', $remote['remote_id'] ?? '');
        }

        if (! isset($local['published']) && isset($fillable['published'])) {
            $local['published'] = 1;
        }

        return $local;
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $attributes[$key] = $value->format('Y-m-d H:i:s');
            }
        }

        return $attributes;
    }

    private function resolveRecordLabel(Model $record): string
    {
        foreach (['name', 'title', 'label'] as $attribute) {
            $value = $record->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return sprintf('#%s', $record->getKey());
    }
}
