<?php

namespace Unusualify\Modularous\Entities\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Unusualify\Modularous\Entities\Enums\Permission;
use Unusualify\Modularous\Entities\Enums\RevisionStatus;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Module;

trait HasRevisions
{
    /**
     * Override and return true together with {@see revisionPermissionPrefix()} to enable approval workflow.
     * This property is used to check if the revision workflow is enabled for the model.
     * Do not use a model property named revisionWorkflowEnabled — Eloquent resolves it as this method (relationship).
     */
    protected function revisionWorkflowEnabled(): bool
    {
        return $this->isRevisionWorkflowEnabled ?? false;
    }

    /**
     * Kebab-case route segment for permissions, e.g. "page" → "page_revision_approve".
     * Override in the composed model; do not redeclare as a property.
     */
    protected function revisionPermissionPrefix(): ?string
    {
        if (method_exists($this, 'getModule') && ($module = $this->getModule()) instanceof Module) {
            $routeName = $this->getRouteName();

            return snakeCase($routeName);
        }

        return null;
    }

    /**
     * Defines the one-to-many relationship for revisions.
     *
     * @return HasMany
     */
    public function revisions()
    {
        return $this->hasMany($this->getRevisionModel())
            ->orderBy('created_at', 'desc')
            ->with(['user', 'source']);
    }

    /**
     * Latest revision row by id (the only row that may be {@see RevisionStatus::Pending} when workflow is on).
     */
    public function latestRevision(): HasOne
    {
        return $this->hasOne($this->getRevisionModel())->latestOfMany('id');
    }

    public function usesRevisionWorkflow(): bool
    {
        return $this->revisionWorkflowEnabled() === true
            && is_string($this->revisionPermissionPrefix())
            && $this->revisionPermissionPrefix() !== '';
    }

    /**
     * Id of the current pending revision when the newest revision row has status pending; otherwise null.
     */
    public function getPendingRevisionId(): ?int
    {
        $revisionModel = $this->getRevisionModel();
        $instance = new $revisionModel;

        if (! Schema::hasColumn($instance->getTable(), 'status')) {
            return null;
        }

        $latest = $this->revisions()->orderByDesc('id')->first();

        if (! $latest || ($latest->status ?? RevisionStatus::Approved->value) !== RevisionStatus::Pending->value) {
            return null;
        }

        return (int) $latest->id;
    }

    /**
     * True when the newest revision row is pending. That state locks update and restore.
     */
    public function isRevisionWorkflowLocked(): bool
    {
        if (! $this->usesRevisionWorkflow()) {
            return false;
        }

        return $this->latestRevisionIsPending();
    }

    /**
     * @deprecated Use {@see isRevisionWorkflowLocked()} for workflow models.
     */
    public function hasPendingRevision(): bool
    {
        return $this->isRevisionWorkflowLocked();
    }

    protected function latestRevisionIsPending(): bool
    {
        $revisionModel = $this->getRevisionModel();
        $instance = new $revisionModel;

        if (! Schema::hasColumn($instance->getTable(), 'status')) {
            return false;
        }

        $latest = $this->revisions()->orderByDesc('id')->first();

        if (! $latest) {
            return false;
        }

        return ($latest->status ?? RevisionStatus::Approved->value) === RevisionStatus::Pending->value;
    }

    public function userCanApproveRevisions(): bool
    {
        if (! $this->usesRevisionWorkflow()) {
            return true;
        }

        $user = Auth::guard(Modularous::getAuthGuardName())->user();

        return $user && Gate::forUser($user)->allows(Permission::generatePermissionName('REVISION_APPROVE', $this->revisionPermissionPrefix()));
    }

    public function userCanRejectRevisions(): bool
    {
        if (! $this->usesRevisionWorkflow()) {
            return true;
        }

        $user = Auth::guard(Modularous::getAuthGuardName())->user();

        return $user && Gate::forUser($user)->allows(Permission::generatePermissionName('REVISION_REJECT', $this->revisionPermissionPrefix()));
    }

    public function userCanRestoreRevisions(): bool
    {
        if (! $this->usesRevisionWorkflow()) {
            return true;
        }

        $user = Auth::guard(Modularous::getAuthGuardName())->user();

        return $user && Gate::forUser($user)->allows(Permission::generatePermissionName('REVISION_RESTORE', $this->revisionPermissionPrefix()));
    }

    /**
     * Scope a query to only include the current user's revisions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeMine($query)
    {
        $user = Auth::guard(Modularous::getAuthGuardName())->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('revisions', function ($query) {
            $query->where('user_id', Auth::guard(Modularous::getAuthGuardName())->id());
        });
    }

    /**
     * Returns an array of revisions for the CMS views.
     *
     * @return array
     */
    public function revisionsArray()
    {
        $revisions = $this->revisions; // ordered DESC (newest first)
        $total = $revisions->count();

        $versionMap = $revisions->mapWithKeys(function ($revision, $index) use ($total) {
            return [$revision->id => $total - $index];
        });

        return $revisions
            ->map(function ($revision, $index) use ($total, $versionMap) {
                $sourceLabel = $revision->source_id && isset($versionMap[$revision->source_id])
                    ? 'V' . $versionMap[$revision->source_id]
                    : null;

                return [
                    'id' => $revision->id,
                    'author' => $revision->user->name ?? 'Unknown',
                    'datetime' => $revision->created_at->toIso8601String(),
                    'label' => 'V' . ($total - $index),
                    'source_label' => $sourceLabel,
                    'is_restored' => (bool) $revision->source_id,
                    'source_datetime' => $revision->source?->created_at?->toIso8601String(),
                    'status' => $revision->status ?? 'approved',
                ];
            })
            ->toArray();
    }

    /**
     * Deletes revisions from specific collection position
     * Used to keep max revision on specific Twill's module.
     */
    public function deleteSpecificRevisions(int $maxRevisions): void
    {
        if (isset($this->limitRevisions) && $this->limitRevisions > 0) {
            $maxRevisions = $this->limitRevisions;
        }

        $this->revisions()->get()->slice($maxRevisions)->each->delete();
    }

    public function getRevisionModel()
    {
        if (property_exists($this, 'revisionModel') && is_string($this->revisionModel) && @class_exists($this->revisionModel)) {
            return $this->revisionModel;
        }

        $modelClass = get_class($this);
        $candidates = [
            preg_replace('/\\\\Entities\\\\([^\\\\]+)$/', '\\Entities\\Revisions\\$1Revision', $modelClass),
            modularousConfig('namespace') . '\\Models\\Revisions\\' . class_basename($this) . 'Revision',
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && @class_exists($candidate)) {
                return $candidate;
            }
        }

        throw new RuntimeException("Revision model could not be resolved for [{$modelClass}]. Define a \$revisionModel property.");
    }
}
