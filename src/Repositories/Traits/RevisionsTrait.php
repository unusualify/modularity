<?php

namespace Unusualify\Modularous\Repositories\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Enums\RevisionStatus;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Facades\ValidationException;
use Unusualify\Modularous\Models\Model;

trait RevisionsTrait
{
    protected bool $skipRevisionCreation = false;

    protected ?int $pendingSourceRevisionId = null;

    protected bool $workflowBypassPendingGuard = false;

    /**
     * Runtime flags for MethodTransformers::afterSave: when true, that hook is skipped.
     * Declared here; set by bypassAfterSaves() when a composing trait opts in via pendingBypassRevision* (see each trait).
     *
     * @see resetPassAfterSaves()
     */
    protected bool $passAfterSaveSlugsTrait = false;

    protected bool $passAfterSaveFilesTrait = false;

    protected bool $passAfterSaveImagesTrait = false;

    protected bool $passAfterSaveFilepondsTrait = false;

    protected bool $passAfterSaveRepeatersTrait = false;

    protected bool $passAfterSavePricesTrait = false;

    protected bool $passAfterSaveTagsTrait = false;

    protected bool $passAfterSaveRelationships = false;

    protected bool $passAfterSavePaymentTrait = false;

    public function update($id, $fields, $schema = null, $options = [])
    {
        $this->setSchema($schema);
        $this->setColumns($schema ?? $this->chunkInputs(all: true));

        $object = $this->resolveRevisionSubject($id);

        if ($this->shouldQueuePendingRevisionOnly($object, $fields)) {
            $this->beforeSave($object, $fields);

            $preparedFields = $this->prepareFieldsBeforeSave($object, $fields);

            if ($this->processPendingRevisionSubmission($object, $preparedFields)) {
                $object = $this->touchEloquentModel($object->fresh());
                $this->dispatchEvent($object, 'update');

                return true;
            }
        }

        return parent::update($id, $fields, $schema, $options);
    }

    /**
     * Resolve the subject row for revision operations (IsSingular → {@see single()}).
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function resolveRevisionSubject(int $id)
    {
        if (classHasTrait($this->model, 'Unusualify\Modularous\Entities\Traits\IsSingular')) {
            return $this->model->single();
        }

        return $this->model->findOrFail($id);
    }

    public function beforeSaveRevisionsTrait($object, $fields): void
    {
        if (! method_exists($object, 'usesRevisionWorkflow') || ! $object->usesRevisionWorkflow()) {
            return;
        }

        if ($this->workflowBypassPendingGuard) {
            return;
        }

        if (method_exists($object, 'isRevisionWorkflowLocked') && $object->isRevisionWorkflowLocked()) {
            $message = __('messages.revision.pending-locks-record');

            throw ValidationException::withMessages([
                'revision' => [$message],
            ])->variant('warning');
        }
    }

    public function afterSaveRevisionsTrait($object, $fields): void
    {
        $this->createRevisionIfNeeded($object, $fields);
    }

    /**
     * @param Model $object
     * @return array
     */
    public function getFormFieldsRevisionsTrait($object, $fields, $schema = [])
    {
        // set, cast, unset or manipulate the fields by using object, fields and schema
        if (isset($schema['revisionable_id'])) {
            $fields['revisionable_id'] = $object?->id;
        }

        return $fields;
    }

    public function createRevisionIfNeeded($object, array $fields): array
    {
        if ($this->skipRevisionCreation) {
            return $fields;
        }

        $lastRevisionPayload = $this->getLastApprovedRevisionPayload($object);

        $fullPayload = array_replace_recursive($lastRevisionPayload, $fields);

        if ($this->revisionPayloadsAreEquivalent($fullPayload, $lastRevisionPayload)) {
            return $fields;
        }

        $userId = Auth::guard(Modularous::getAuthGuardName())->id() ?? Auth::id();

        $revisionAttributes = [
            'payload' => json_encode($fullPayload),
            'user_id' => $userId,
            'source_id' => $this->pendingSourceRevisionId,
        ];

        if ($this->revisionTableHasStatusColumn($object)) {
            $this->applyApprovedRevisionAttributes($revisionAttributes, $userId);
        }

        $object->revisions()->create($revisionAttributes);

        if (isset($object->limitRevisions) && (int) $object->limitRevisions > 0) {
            $object->deleteSpecificRevisions((int) $object->limitRevisions);
        }

        return $fields;
    }

    public function preview(int $id, array $fields)
    {
        $object = $this->resolveRevisionSubject($id);

        return $this->hydrateObject($object, $fields);
    }

    public function previewForRevision(int $id, int $revisionId, $schema = [])
    {
        $this->setSchema($schema);
        $this->setColumns($schema ?? $this->chunkInputs(all: true));

        $object = $this->resolveRevisionSubject($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();
        $fields = json_decode($revision->payload, true) ?: [];
        $subjectId = (int) $object->getKey();

        return $this->hydrateObject($this->model->newInstance()->setAttribute('id', $subjectId), $fields);
    }

    public function restoreRevision(int $id, int $revisionId)
    {
        $object = $this->resolveRevisionSubject($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();

        if ($this->revisionTableHasStatusColumn($object) && ($revision->status ?? RevisionStatus::Approved->value) === RevisionStatus::Rejected->value) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.restore-blocked-rejected')],
            ]);
        }

        if (method_exists($object, 'usesRevisionWorkflow') && $object->usesRevisionWorkflow()) {
            if (method_exists($object, 'isRevisionWorkflowLocked') && $object->isRevisionWorkflowLocked()) {
                throw ValidationException::withMessages([
                    'revision' => [__('messages.revision.restore-blocked-pending')],
                ]);
            }

            if (! $object->userCanRestoreRevisions()) {
                throw ValidationException::withMessages([
                    'revision' => [__('messages.revision.restore-forbidden')],
                ]);
            }
        }

        if ($revision->source_id !== null) {
            abort(422, __('messages.revision.restore-disabled-already-restored'));
        }

        $fields = json_decode($revision->payload, true) ?: [];
        $subjectId = (int) $object->getKey();

        if ($this->shouldRestoreAsPendingOnly($object)) {
            return $this->restoreRevisionAsPendingOnly($object, $fields, $revisionId);
        }

        // Skip auto-revision creation during update so we can force-create one below,
        // ensuring a restore is always recorded even when content is identical to the latest revision.
        $this->skipRevisionCreation = true;
        $this->update($subjectId, $fields);
        $this->skipRevisionCreation = false;

        $userId = Auth::guard(Modularous::getAuthGuardName())->id() ?? Auth::id();
        $restoreAttributes = [
            'payload' => json_encode($fields),
            'user_id' => $userId,
            'source_id' => $revisionId,
        ];
        if ($this->revisionTableHasStatusColumn($object)) {
            $this->applyApprovedRevisionAttributes($restoreAttributes, $userId);
        }

        $object->revisions()->create($restoreAttributes);

        return $this->resolveRevisionSubject($subjectId);
    }

    /**
     * Workflow on + user lacks {@code *_revision_approve}: restore only queues a pending snapshot (subject row unchanged), like a normal edit.
     *
     * @param Model $object
     */
    protected function shouldRestoreAsPendingOnly($object): bool
    {
        if (! $this->revisionTableHasStatusColumn($object)) {
            return false;
        }

        if (! method_exists($object, 'usesRevisionWorkflow') || ! $object->usesRevisionWorkflow()) {
            return false;
        }

        return ! $object->userCanApproveRevisions();
    }

    /**
     * Record a proposed restore as the latest pending revision without persisting payload to the subject.
     *
     * @param Model $object
     */
    protected function restoreRevisionAsPendingOnly($object, array $fields, int $sourceRevisionId): mixed
    {
        if (method_exists($object, 'isRevisionWorkflowLocked') && $object->isRevisionWorkflowLocked()) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.pending-only-one')],
            ]);
        }

        $this->skipRevisionCreation = true;

        try {
            $userId = Auth::guard(Modularous::getAuthGuardName())->id() ?? Auth::id();

            $revisionAttributes = [
                'payload' => json_encode($fields),
                'user_id' => $userId,
                'source_id' => $sourceRevisionId,
            ];

            if ($this->revisionTableHasStatusColumn($object)) {
                $revisionAttributes['status'] = RevisionStatus::Pending->value;
                $revisionAttributes['approved_at'] = null;
                $revisionAttributes['approved_by'] = null;
            }

            $object->revisions()->create($revisionAttributes);

            $this->bypassAfterSaves();
            try {
                $this->afterSave($object, $fields);
            } finally {
                $this->resetPassAfterSaves();
            }
        } finally {
            $this->skipRevisionCreation = false;
        }

        return $this->resolveRevisionSubject((int) $object->getKey());
    }

    /**
     * Sets passAfterSave* flags when a composing trait opts in via traitProperties('pendingBypassRevision').
     * Only traits that declare pendingBypassRevision{TraitBasename} participate; when that flag is true,
     * passAfterSave{SameSuffix} is set so the corresponding afterSave hook is skipped during pending-only saves.
     *
     * File / Filepond: when bypassed, Filepond::saveFile does not run; the revision JSON must still store upload
     * response metadata (ids, paths). On approve, a normal afterSave finalizes. Mitigate temp expiry via longer TTL,
     * staging disk, or a dedicated “promote temp file to library without attaching to live row” step.
     */
    protected function bypassAfterSaves(): void
    {
        foreach ($this->traitProperties('pendingBypassRevision') as $pendingKey) {
            if (! $this->{$pendingKey}) {
                continue;
            }

            $suffix = (string) preg_replace('/^pendingBypassRevision/', '', $pendingKey);

            if ($suffix === 'RevisionsTrait') {
                continue;
            }

            $passKey = 'passAfterSave' . $suffix;

            if (property_exists($this, $passKey)) {
                $this->{$passKey} = true;
            }
        }
    }

    protected function resetPassAfterSaves(): void
    {
        foreach ($this->traitProperties('passAfterSave') as $passKey) {
            $this->{$passKey} = false;
        }
    }

    /**
     * @param array<string, mixed> $attributes
     */
    protected function applyApprovedRevisionAttributes(array &$attributes, $userId): void
    {
        $attributes['status'] = RevisionStatus::Approved->value;
        $attributes['approved_at'] = now();
        $attributes['approved_by'] = $userId;
    }

    /**
     * Apply a pending revision payload to the subject and mark the revision approved.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function approveRevision(int $id, int $revisionId)
    {
        $object = $this->resolveRevisionSubject($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();

        if (! method_exists($object, 'usesRevisionWorkflow') || ! $object->usesRevisionWorkflow()) {
            abort(422, __('messages.revision.approve-not-applicable'));
        }

        $latest = $object->revisions()->orderByDesc('id')->first();

        if (! $latest || (int) $latest->id !== (int) $revision->id) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.approve-not-latest')],
            ]);
        }

        if ($this->revisionTableHasStatusColumn($object) && ! $revision->isPending()) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.approve-not-pending')],
            ]);
        }

        $fields = json_decode($revision->payload, true) ?: [];
        $subjectId = (int) $object->getKey();

        $this->workflowBypassPendingGuard = true;
        $this->skipRevisionCreation = true;

        try {
            $this->update($subjectId, $fields);

            if ($this->revisionTableHasStatusColumn($object)) {
                $revision->refresh();
                $revision->update([
                    'status' => RevisionStatus::Approved->value,
                    'approved_at' => now(),
                    'approved_by' => Auth::guard(Modularous::getAuthGuardName())->id() ?? Auth::id(),
                ]);
            }
        } finally {
            $this->workflowBypassPendingGuard = false;
            $this->skipRevisionCreation = false;
        }

        return $this->resolveRevisionSubject($subjectId);
    }

    /**
     * Mark the latest pending revision as rejected. Does not update the subject row.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function rejectRevision(int $id, int $revisionId)
    {
        $object = $this->resolveRevisionSubject($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();

        if (! method_exists($object, 'usesRevisionWorkflow') || ! $object->usesRevisionWorkflow()) {
            abort(422, __('messages.revision.reject-not-applicable'));
        }

        $latest = $object->revisions()->orderByDesc('id')->first();

        if (! $latest || (int) $latest->id !== (int) $revision->id) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.reject-not-latest')],
            ]);
        }

        if ($this->revisionTableHasStatusColumn($object) && ! $revision->isPending()) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.reject-not-pending')],
            ]);
        }

        if ($this->revisionTableHasStatusColumn($object)) {
            $revision->update([
                'status' => RevisionStatus::Rejected->value,
                'approved_at' => null,
                'approved_by' => null,
            ]);
        }

        return $this->resolveRevisionSubject((int) $object->getKey());
    }

    public function getRevisionPayload(int $id, int $revisionId): array
    {
        $object = $this->resolveRevisionSubject($id);
        $revision = $object->revisions()->where('id', $revisionId)->firstOrFail();

        return json_decode($revision->payload, true) ?: [];
    }

    public function getCountForMine(): int
    {
        $query = $this->model->newQuery();

        return $this->filter($query, $this->countScope)->mine()->count();
    }

    public function getCountByStatusSlugRevisionsTrait(string $slug): int|bool
    {
        if ($slug === 'mine') {
            return $this->getCountForMine();
        }

        return false;
    }

    protected function hydrateObject($object, array $fields)
    {
        $fields = $this->prepareFieldsBeforeSave($object, $fields);
        $object->fill(Arr::except($fields, $this->getReservedFields()));

        return $this->hydrate($object, $fields);
    }

    public function getRevisions(int $id)
    {
        $object = $this->resolveRevisionSubject($id);
        $revisionModel = $object->getRevisionModel();
        $foreignKey = method_exists($object, 'getRevisionForeignKey')
            ? $object->getRevisionForeignKey()
            : $object->getForeignKey();

        return $revisionModel::where($foreignKey, $object->getKey())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    protected function shouldQueuePendingRevisionOnly($object, array $fields): bool
    {
        if (! method_exists($object, 'usesRevisionWorkflow') || ! $object->usesRevisionWorkflow()) {
            return false;
        }

        if ($this->workflowBypassPendingGuard) {
            return false;
        }

        if (! $object->userCanApproveRevisions()) {
            return true;
        }

        return false;
    }

    /**
     * @param Model $object
     * @return bool false when merged payload matches last approved (nothing new to queue)
     */
    protected function processPendingRevisionSubmission($object, array $fields): bool
    {
        if (method_exists($object, 'isRevisionWorkflowLocked') && $object->isRevisionWorkflowLocked()) {
            throw ValidationException::withMessages([
                'revision' => [__('messages.revision.pending-only-one')],
            ]);
        }

        $lastPayload = $this->getLastApprovedRevisionPayload($object);
        $fullPayload = array_replace_recursive($lastPayload, $fields);

        if ($this->revisionPayloadsAreEquivalent($fullPayload, $lastPayload)) {
            return false;
        }

        $this->skipRevisionCreation = true;

        try {
            $userId = Auth::guard(Modularous::getAuthGuardName())->id() ?? Auth::id();

            $revisionAttributes = [
                'payload' => json_encode($fullPayload),
                'user_id' => $userId,
                'source_id' => $this->pendingSourceRevisionId,
            ];

            if ($this->revisionTableHasStatusColumn($object)) {
                $revisionAttributes['status'] = RevisionStatus::Pending->value;
                $revisionAttributes['approved_at'] = null;
                $revisionAttributes['approved_by'] = null;
            }

            $object->revisions()->create($revisionAttributes);

            $this->bypassAfterSaves();
            try {
                $this->afterSave($object, $fields);
            } finally {
                $this->resetPassAfterSaves();
            }

            return true;
        } finally {
            $this->skipRevisionCreation = false;
        }
    }

    /**
     * Compare revision payloads without regard to associative key order (PHP's array === is order-sensitive).
     *
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    protected function revisionPayloadsAreEquivalent(array $a, array $b): bool
    {
        $na = $this->normalizeRevisionPayloadForComparison($a);
        $nb = $this->normalizeRevisionPayloadForComparison($b);

        return json_encode($na, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            === json_encode($nb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param array<string, mixed> $value
     * @return array<int|string, mixed>|mixed
     */
    protected function normalizeRevisionPayloadForComparison(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->normalizeRevisionPayloadForComparison($item), $value);
        }

        ksort($value);

        foreach ($value as $k => $v) {
            $value[$k] = $this->normalizeRevisionPayloadForComparison($v);
        }

        return $value;
    }

    /**
     * @param Model $object
     */
    protected function revisionTableHasStatusColumn($object): bool
    {
        $modelClass = $object->getRevisionModel();
        $instance = new $modelClass;

        return Schema::hasColumn($instance->getTable(), 'status');
    }

    /**
     * Payload merged from the latest approved (or legacy unmarked) revision.
     *
     * @param Model $object
     * @return array<string, mixed>
     */
    public function getLastApprovedRevisionPayload($object): array
    {
        $query = $object->revisions()->orderByDesc('id');

        if ($this->revisionTableHasStatusColumn($object)) {
            $query->where(function ($q) {
                $q->where('status', RevisionStatus::Approved->value)
                    ->orWhereNull('status');
            });
        }

        $revision = $query->first();

        return json_decode($revision->payload ?? '{}', true) ?: [];
    }

    /**
     * @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    public function appendFormSchemaRevisionsTrait($scope = []): array
    {
        return [
            [
                'type' => 'revision',
                'maxHeight' => '150px',
            ],
        ];
    }
}
