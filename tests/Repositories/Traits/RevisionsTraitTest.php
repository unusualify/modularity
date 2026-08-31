<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Repositories\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularous\Entities\Revision;
use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Exceptions\ValidationException;
use Unusualify\Modularous\Repositories\Repository;
use Unusualify\Modularous\Repositories\Traits\RevisionsTrait;
use Unusualify\Modularous\Tests\ModelTestCase;
use Unusualify\Modularous\Traits\Traitify;

class RevisionsTraitTest extends ModelTestCase
{
    use RefreshDatabase;

    protected RepositoryUsingRevisionsTrait $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRevisionTables();
        $this->repository = new RepositoryUsingRevisionsTrait(new RevisionsTraitTestModel);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('revisions_trait_test_revisions');
        Schema::dropIfExists('revisions_trait_test_models');

        parent::tearDown();
    }

    public function test_revision_payloads_are_equivalent_ignores_key_order(): void
    {
        $equivalent = $this->repository->invokeRevisionPayloadsAreEquivalent(
            ['title' => 'A', 'meta' => ['z' => 1, 'a' => 2]],
            ['meta' => ['a' => 2, 'z' => 1], 'title' => 'A'],
        );

        $this->assertTrue($equivalent);
        $this->assertFalse($this->repository->invokeRevisionPayloadsAreEquivalent(
            ['title' => 'A'],
            ['title' => 'B'],
        ));
    }

    public function test_get_form_fields_revisions_trait_sets_revisionable_id(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);

        $fields = $this->repository->getFormFieldsRevisionsTrait($object, [], [
            'revisionable_id' => true,
        ]);

        $this->assertSame($object->id, $fields['revisionable_id']);
    }

    public function test_create_revision_if_needed_skips_when_payload_unchanged(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);
        $object->revisions()->create([
            'payload' => json_encode(['title' => 'Item']),
            'user_id' => null,
        ]);

        $this->repository->createRevisionIfNeeded($object, ['title' => 'Item']);

        $this->assertSame(1, $object->revisions()->count());
    }

    public function test_create_revision_if_needed_creates_new_revision(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);

        $this->repository->createRevisionIfNeeded($object, ['title' => 'Updated']);

        $this->assertSame(1, $object->revisions()->count());
        $this->assertSame(
            'Updated',
            json_decode((string) $object->revisions()->first()->payload, true)['title']
        );
    }

    public function test_get_last_approved_revision_payload_returns_latest_approved(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);
        $object->revisions()->create([
            'payload' => json_encode(['title' => 'Old']),
            'user_id' => null,
            'status' => 'approved',
        ]);
        $object->revisions()->create([
            'payload' => json_encode(['title' => 'Latest']),
            'user_id' => null,
            'status' => 'approved',
        ]);

        $payload = $this->repository->getLastApprovedRevisionPayload($object);

        $this->assertSame('Latest', $payload['title']);
    }

    public function test_get_revision_payload_returns_decoded_payload(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Snapshot']),
            'user_id' => null,
        ]);

        $payload = $this->repository->getRevisionPayload($object->id, $revision->id);

        $this->assertSame(['title' => 'Snapshot'], $payload);
    }

    public function test_get_count_by_status_slug_revisions_trait_returns_false_for_unknown_slug(): void
    {
        $this->assertFalse($this->repository->getCountByStatusSlugRevisionsTrait('published'));
    }

    public function test_apply_approved_revision_attributes_sets_status_metadata(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsTraitTestModel);
        $attributes = [];

        $repository->invokeApplyApprovedRevisionAttributes($attributes, 42);

        $this->assertSame('approved', $attributes['status']);
        $this->assertSame(42, $attributes['approved_by']);
        $this->assertNotNull($attributes['approved_at']);
    }

    public function test_get_revisions_returns_rows_for_subject(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'History']);
        $object->revisions()->create([
            'payload' => json_encode(['title' => 'V1']),
            'user_id' => null,
        ]);
        $object->revisions()->create([
            'payload' => json_encode(['title' => 'V2']),
            'user_id' => null,
        ]);

        $revisions = $this->repository->getRevisions($object->id);

        $this->assertCount(2, $revisions);
    }

    public function test_restore_revision_applies_payload_and_records_source(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Current']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Restored']),
            'user_id' => null,
            'status' => 'approved',
        ]);

        $this->repository->restoreRevision($object->id, $revision->id);

        $this->assertSame('Restored', $object->fresh()->title);
        $this->assertSame(
            $revision->id,
            $object->fresh()->revisions()->orderByDesc('id')->first()->source_id
        );
    }

    public function test_preview_hydrates_fields_without_persisting(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Original']);
        $preview = $this->repository->preview($object->id, ['title' => 'Preview only']);

        $this->assertSame('Preview only', $preview->title);
        $this->assertSame('Original', $object->fresh()->title);
    }

    public function test_before_save_revisions_trait_returns_when_workflow_disabled(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);
        $this->repository->beforeSaveRevisionsTrait($object, ['title' => 'X']);
        $this->assertTrue(true);
    }

    public function test_before_save_revisions_trait_throws_when_workflow_locked(): void
    {
        $object = new RevisionsWorkflowLockedModel(['title' => 'Locked']);
        $this->expectException(ValidationException::class);
        $this->repository->beforeSaveRevisionsTrait($object, ['title' => 'X']);
    }

    public function test_create_revision_if_needed_skips_when_flag_set(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Item']);
        $this->repository->setSkipRevisionCreation(true);
        $this->repository->createRevisionIfNeeded($object, ['title' => 'Changed']);
        $this->assertSame(0, $object->revisions()->count());
        $this->repository->setSkipRevisionCreation(false);
    }

    public function test_preview_for_revision_hydrates_snapshot(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Live']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Snapshot']),
            'user_id' => null,
        ]);

        $preview = $this->repository->previewForRevision($object->id, $revision->id);
        $this->assertSame('Snapshot', $preview->title);
        $this->assertSame('Live', $object->fresh()->title);
    }

    public function test_reject_revision_marks_latest_pending(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsWorkflowTestModel);
        $object = RevisionsWorkflowTestModel::create(['title' => 'Item']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Pending']),
            'user_id' => null,
            'status' => 'pending',
        ]);

        $repository->rejectRevision($object->id, $revision->id);

        $this->assertSame('rejected', $revision->fresh()->status);
    }

    public function test_bypass_and_reset_pass_after_saves_toggle_flags(): void
    {
        $this->repository->pendingBypassRevisionFilesTrait = true;
        $this->repository->invokeBypassAfterSaves();
        $this->assertTrue($this->repository->passesAfterSaveFilesTrait());

        $this->repository->invokeResetPassAfterSaves();
        $this->assertFalse($this->repository->passesAfterSaveFilesTrait());
    }

    public function test_get_count_by_status_slug_mine_uses_filter_path(): void
    {
        RevisionsTraitTestModel::create(['title' => 'Mine']);
        $count = $this->repository->getCountByStatusSlugRevisionsTrait('mine');
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function test_should_queue_pending_revision_only_for_workflow_without_approve(): void
    {
        $object = new RevisionsWorkflowNoApproveModel(['title' => 'Item']);
        $this->assertTrue($this->repository->invokeShouldQueuePendingRevisionOnly($object, ['title' => 'X']));
    }

    public function test_approve_revision_applies_payload_and_marks_approved(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsWorkflowTestModel);
        $object = RevisionsWorkflowTestModel::create(['title' => 'Live']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Approved Title']),
            'user_id' => null,
            'status' => 'pending',
        ]);

        $repository->approveRevision($object->id, $revision->id);

        $this->assertSame('Approved Title', $object->fresh()->title);
        $this->assertSame('approved', $revision->fresh()->status);
    }

    public function test_process_pending_revision_submission_queues_without_mutating_subject(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsWorkflowNoApproveModel);
        $object = RevisionsWorkflowNoApproveModel::create(['title' => 'Original']);

        $method = new \ReflectionMethod($repository, 'processPendingRevisionSubmission');
        $method->setAccessible(true);
        $queued = $method->invoke($repository, $object, ['title' => 'Queued']);

        $this->assertTrue($queued);
        $this->assertSame('Original', $object->fresh()->title);
        $pending = $object->revisions()->where('status', 'pending')->first();
        $this->assertNotNull($pending);
        $this->assertSame('Queued', json_decode((string) $pending->payload, true)['title'] ?? null);
    }

    public function test_after_save_revisions_trait_creates_revision(): void
    {
        $object = RevisionsTraitTestModel::create(['title' => 'Base']);
        $this->repository->afterSaveRevisionsTrait($object, ['title' => 'After']);
        $this->assertSame(1, $object->revisions()->count());
    }

    public function test_restore_revision_as_pending_only_path(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsWorkflowNoApproveModel);
        $object = RevisionsWorkflowNoApproveModel::create(['title' => 'Current']);
        $revision = $object->revisions()->create([
            'payload' => json_encode(['title' => 'Pending Restore']),
            'user_id' => null,
            'status' => 'approved',
        ]);

        $method = new \ReflectionMethod($repository, 'restoreRevisionAsPendingOnly');
        $method->setAccessible(true);
        $method->invoke($repository, $object, ['title' => 'Pending Restore'], (int) $revision->id);

        $this->assertSame('Current', $object->fresh()->title);
        $this->assertGreaterThanOrEqual(1, $object->revisions()->where('status', 'pending')->count());
    }

    public function test_should_restore_as_pending_only_helpers(): void
    {
        $repository = new RepositoryUsingRevisionsTrait(new RevisionsWorkflowNoApproveModel);
        $object = RevisionsWorkflowNoApproveModel::create(['title' => 'Item']);
        $method = new \ReflectionMethod($repository, 'shouldRestoreAsPendingOnly');
        $method->setAccessible(true);
        $this->assertTrue($method->invoke($repository, $object));

        $plain = RevisionsTraitTestModel::create(['title' => 'Plain']);
        $this->assertFalse($method->invoke($this->repository, $plain));
    }

    protected function createRevisionTables(): void
    {
        Schema::create('revisions_trait_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
        });

        Schema::create('revisions_trait_test_revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('revisions_trait_test_model_id');
            $table->longText('payload')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
        });
    }
}

class RevisionsTraitTestModel extends Model
{
    use HasRevisions;

    protected $table = 'revisions_trait_test_models';

    protected $fillable = ['title'];

    protected string $revisionModel = RevisionsTraitTestRevision::class;
}

class RevisionsTraitTestRevision extends Revision
{
    protected $table = 'revisions_trait_test_revisions';
}

class RevisionsWorkflowTestModel extends RevisionsTraitTestModel
{
    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel(), 'revisions_trait_test_model_id');
    }

    protected function revisionWorkflowEnabled(): bool
    {
        return true;
    }

    protected function revisionPermissionPrefix(): ?string
    {
        return 'revisions_trait_test_model';
    }

    public function userCanApproveRevisions(): bool
    {
        return true;
    }
}

class RevisionsWorkflowLockedModel extends RevisionsWorkflowTestModel
{
    public function usesRevisionWorkflow(): bool
    {
        return true;
    }

    public function isRevisionWorkflowLocked(): bool
    {
        return true;
    }
}

class RevisionsWorkflowNoApproveModel extends RevisionsWorkflowTestModel
{
    public function usesRevisionWorkflow(): bool
    {
        return true;
    }

    public function userCanApproveRevisions(): bool
    {
        return false;
    }
}

trait FilesTrait {}

class RepositoryUsingRevisionsTrait extends Repository
{
    use RevisionsTrait;
    use Traitify;
    use FilesTrait;

    public bool $pendingBypassRevisionFilesTrait = false;

    public function __construct(RevisionsTraitTestModel $model)
    {
        $this->model = $model;
        $this->countScope = [];
    }

    /**
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    public function invokeRevisionPayloadsAreEquivalent(array $a, array $b): bool
    {
        return $this->revisionPayloadsAreEquivalent($a, $b);
    }

    public function invokeBypassAfterSaves(): void
    {
        $this->bypassAfterSaves();
    }

    public function invokeResetPassAfterSaves(): void
    {
        $this->resetPassAfterSaves();
    }

    public function passesAfterSaveFilesTrait(): bool
    {
        return $this->passAfterSaveFilesTrait;
    }

    public function setPassAfterSaveSlugsTrait(bool $value): void
    {
        $this->passAfterSaveSlugsTrait = $value;
    }

    public function invokeApplyApprovedRevisionAttributes(array &$attributes, $userId): void
    {
        $this->applyApprovedRevisionAttributes($attributes, $userId);
    }

    public function invokeShouldQueuePendingRevisionOnly($object, array $fields): bool
    {
        return $this->shouldQueuePendingRevisionOnly($object, $fields);
    }

    public function setSkipRevisionCreation(bool $value): void
    {
        $this->skipRevisionCreation = $value;
    }

    public function filter($query, $scopes = [])
    {
        return new class($query)
        {
            public function __construct(private $query) {}

            public function mine()
            {
                return $this->query;
            }

            public function count(): int
            {
                return $this->query->count();
            }
        };
    }

    public function afterSave($object, $fields): void {}

    public function update($id, $fields, $schema = null, $options = [])
    {
        $object = $this->model->findOrFail($id);
        $object->fill(array_intersect_key($fields, array_flip($object->getFillable())));
        $object->save();
        $this->afterSave($object, $fields);

        return true;
    }

    protected function hydrateObject($object, array $fields)
    {
        $object->fill(array_intersect_key($fields, array_flip($object->getFillable())));

        return $object;
    }

    public function hydrate($object, $fields)
    {
        return $object;
    }

    public function getReservedFields(): array
    {
        return [];
    }

    public function prepareFieldsBeforeSave($object, $fields): array
    {
        return $fields;
    }

    public function chunkInputs($schema = null, $all = false, $noGroupChunk = false): array
    {
        return [];
    }
}
