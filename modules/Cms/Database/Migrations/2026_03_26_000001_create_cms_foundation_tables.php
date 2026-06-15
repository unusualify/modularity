<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $pagesTable = modularousConfig('tables.cms_pages', 'um_cms_pages');
        $pageTranslationsTable = modularousConfig('tables.cms_page_translations', 'um_cms_page_translations');
        $pageSlugsTable = modularousConfig('tables.cms_page_slugs', 'um_cms_page_slugs');
        $pageRevisionsTable = modularousConfig('tables.cms_pages_revisions', 'um_cms_pages_revisions');
        $redirectsTable = modularousConfig('tables.cms_redirects', 'um_cms_redirects');
        $siteSettingsTable = modularousConfig('tables.cms_site_settings', 'um_cms_site_settings');
        $searchIndexesTable = modularousConfig('tables.cms_search_indexes', 'um_cms_search_indexes');
        $urlRoutesTable = modularousConfig('tables.cms_url_routes', 'um_cms_url_routes');
        $sitemapsTable = modularousConfig('tables.cms_sitemaps', 'um_cms_sitemaps');
        $sitemapablesTable = modularousConfig('tables.cms_sitemapables', 'um_cms_sitemapables');
        $parentSegmentBindingsTable = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');

        // Legacy two-table layout (replaced by bindings); safe if already rolled back / empty.
        $oldParentSegmentTargetsTable = modularousConfig('tables.cms_parent_segment_targets', 'um_cms_parent_segment_targets');
        $oldParentSegmentsTable = modularousConfig('tables.cms_parent_segments', 'um_cms_parent_segments');
        Schema::dropIfExists($oldParentSegmentTargetsTable);
        Schema::dropIfExists($oldParentSegmentsTable);

        Schema::create($pagesTable, function (Blueprint $table) {
            $table->id();
            $table->string('layout')->nullable();
            $table->json('schema')->nullable();

            $table->string('approval_state')->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            createDefaultExtraTableFields($table, published: true, publishDates: true, visibility: false);

            $table->index(['published', 'approval_state']);
        });

        Schema::create($pageTranslationsTable, function (Blueprint $table) use ($pagesTable) {
            createDefaultTranslationsTableFields($table, 'Page', $pagesTable);
            $table->string('title')->nullable();
            $table->string('slug_segment')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            createTranslatableMetadataFields($table);
        });

        Schema::create($pageSlugsTable, function (Blueprint $table) use ($pagesTable) {
            createDefaultSlugsTableFields($table, 'page', $pagesTable);
        });

        Schema::create($pageRevisionsTable, function (Blueprint $table) use ($pagesTable) {
            createDefaultRevisionsTableFields($table, 'page', $pagesTable);
        });

        Schema::create($redirectsTable, function (Blueprint $table) {
            $table->id();
            $table->string('from_path');
            $table->string('to_path');
            $table->string('locale', 12)->index();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['from_path', 'locale']);
        });

        Schema::create($siteSettingsTable, function (Blueprint $table) {
            $table->id();
            $table->string('group_key');
            $table->string('key');
            $table->string('locale', 12);
            $table->longText('value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['group_key', 'key', 'locale']);
        });

        Schema::create($searchIndexesTable, function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->longText('document');
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id']);
        });

        Schema::create($urlRoutesTable, function (Blueprint $table) {
            $table->id();
            $table->string('locale', 12)->index();
            $table->string('normalized_path', 2048);
            $table->morphs('urlable');
            $table->string('kind', 32)->nullable()->index();
            $table->timestamps();

            $table->unique(['locale', 'normalized_path']);
        });

        Schema::create($sitemapsTable, function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create($sitemapablesTable, function (Blueprint $table) use ($sitemapsTable): void {
            $table->id();
            $table->foreignId('sitemap_id')->constrained($sitemapsTable)->cascadeOnDelete();
            $table->morphs('sitemapable');
            $table->string('changefreq', 32)->nullable();
            $table->decimal('priority', 2, 1)->nullable();
            $table->timestamps();

            $table->unique(
                ['sitemap_id', 'sitemapable_type', 'sitemapable_id'],
                'cms_sitemapables_sitemap_morph_unique'
            );
        });

        $now = now();
        DB::table($sitemapsTable)->insert([
            'id' => 1,
            'slug' => 'default',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        if (DB::getDriverName() === 'pgsql') {
            $sequence = 'um_cms_sitemaps_id_seq';
            DB::statement("SELECT setval('{$sequence}', (SELECT MAX(id) FROM \"{$sitemapsTable}\"))");
        }

        Schema::dropIfExists($parentSegmentBindingsTable);

        Schema::create($parentSegmentBindingsTable, function (Blueprint $table): void {
            $table->id();
            /** @var class-string<\Illuminate\Database\Eloquent\Model> */
            $table->string('target_model_class', 512);
            /** Empty string = all locales */
            $table->string('locale', 12)->default('');
            $table->string('normalized_prefix', 2048);
            $table->string('admin_label')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            // Short names: MySQL identifier limit is 64 chars.
            $table->unique(['target_model_class', 'locale'], 'cms_psb_model_locale_unq');
            $table->index(['target_model_class', 'locale', 'enabled'], 'cms_psb_model_loc_en_idx');

            createDefaultExtraTableFields($table, true, false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings'));
        Schema::dropIfExists(modularousConfig('tables.cms_sitemapables', 'um_cms_sitemapables'));
        Schema::dropIfExists(modularousConfig('tables.cms_sitemaps', 'um_cms_sitemaps'));
        Schema::dropIfExists(modularousConfig('tables.cms_url_routes', 'um_cms_url_routes'));
        Schema::dropIfExists(modularousConfig('tables.cms_pages_revisions', 'um_cms_pages_revisions'));
        Schema::dropIfExists(modularousConfig('tables.cms_page_slugs', 'um_cms_page_slugs'));
        Schema::dropIfExists(modularousConfig('tables.cms_search_indexes', 'um_cms_search_indexes'));
        Schema::dropIfExists(modularousConfig('tables.cms_site_settings', 'um_cms_site_settings'));
        Schema::dropIfExists(modularousConfig('tables.cms_redirects', 'um_cms_redirects'));
        Schema::dropIfExists(modularousConfig('tables.cms_page_translations', 'um_cms_page_translations'));
        Schema::dropIfExists(modularousConfig('tables.cms_pages', 'um_cms_pages'));
    }
};
