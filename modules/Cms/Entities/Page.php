<?php

namespace Modules\Cms\Entities;

use Modules\Cms\Entities\Concerns\IsCmr;
use Modules\Cms\Entities\Revisions\PageRevision;
use Modules\Cms\Entities\Slugs\PageSlug;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasFileponds;
use Unusualify\Modularous\Entities\Traits\HasFiles;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasRepeaters;
use Unusualify\Modularous\Entities\Traits\HasRevisions;
use Unusualify\Modularous\Entities\Traits\HasSlug;
use Unusualify\Modularous\Entities\Traits\HasTranslatableMetadata;
use Unusualify\Modularous\Entities\Traits\HasTranslation;
use Unusualify\Modularous\Entities\Traits\Publishable;

class Page extends Model
{
    use HasRevisions,
        HasSlug,
        HasTranslation,
        HasTranslatableMetadata,
        IsCmr,
        HasFiles,
        HasImages,
        HasFileponds,
        HasRepeaters,
        Publishable;

    protected string $revisionModel = PageRevision::class;

    protected $revisionWorkflowEnabled = true;

    protected $isRevisionWorkflowEnabled = true;

    protected $slugModelClass = PageSlug::class;

    protected $slugForeignKey = 'page_id';

    /**
     * First entry is the source column used to derive URL slugs. If that column is also listed in
     * {@see $translatedAttributes}, {@see HasSlug} reads each locale from translation rows; otherwise the value
     * is taken from the owning model (slug_segment stays on pages while title/excerpt remain translated).
     *
     * @var list<string>
     */
    protected $slugAttributes = [
        'title',
    ];

    public $translatedAttributes = [
        'active',
        'title',
        // 'slug_segment',
        'excerpt',
        'content',
    ];

    protected $fillable = [
        'layout',
        'schema',
        'published',
        'publish_start_date',
        'publish_end_date',
        'approval_state',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'schema' => 'array',
        'published' => 'boolean',
        'approved_at' => 'datetime',
        'publish_start_date' => 'datetime',
        'publish_end_date' => 'datetime',
    ];

    // /**
    //  * Permissions: page_revision_approve, page_revision_restore (see modularous:sync:revision-permissions).
    //  */
    // protected function revisionPermissionPrefix(): ?string
    // {
    //     return 'page';
    // }

    /**
     * When true, editors without approve permission submit pending revisions only; subject row stays locked until approval.
     */
    // protected function revisionWorkflowEnabled(): bool
    // {
    //     return true;
    // }

    /**
     * Base {@see Model} maps null publish_start to "now"; CMS pages use null to clear the schedule.
     */
    public function setPublishStartDateAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['publish_start_date'] = null;

            return;
        }

        $this->attributes['publish_start_date'] = $this->fromDateTime($value);
    }

    /**
     * Clear end date when empty (public visibility has no scheduled end).
     */
    public function setPublishEndDateAttribute(mixed $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['publish_end_date'] = null;

            return;
        }

        $this->attributes['publish_end_date'] = $this->fromDateTime($value);
    }

    public function getTable(): string
    {
        return modularousConfig('tables.cms_pages', 'um_cms_pages');
    }

    // /**
    //  * API / admin binding uses numeric id; front routes resolve by active locale slug via {@see existsSlug()}.
    //  */
    // public function resolveRouteBinding($value, $field = null)
    // {
    //     if ($field === 'id' || $field === $this->getKeyName()) {
    //         return parent::resolveRouteBinding($value, $field);
    //     }

    //     if ($field === null && ctype_digit((string) $value)) {
    //         return static::query()->where($this->getKeyName(), $value)->firstOrFail();
    //     }

    //     return $this->scopes(['published', 'visible'])->existsSlug($value)->firstOrFail();
    // }
}
