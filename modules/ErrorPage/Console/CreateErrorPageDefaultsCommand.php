<?php

declare(strict_types=1);

namespace Modules\ErrorPage\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Services\CmsPageLayoutResolver;
use Modules\ErrorPage\Entities\ErrorPage;
use Modules\ErrorPage\Support\ErrorPageDefaults;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'modularous:create:error-page-defaults')]
class CreateErrorPageDefaultsCommand extends Command
{
    protected $signature = 'modularous:create:error-page-defaults
                            {--layout-slug= : LayoutBuilder slug (falls back to cms_layout_builder.default_layout_slug / default LayoutBuilder)}
                            {--force : Re-publish ErrorPage rows even if they already exist}';

    protected $description = 'Seed default ErrorPage rows (404/403/500) and PageLayout binding for the ErrorPage model';

    public function handle(CmsPageLayoutResolver $pageLayoutResolver): int
    {
        if (! Schema::hasTable((new ErrorPage)->getTable())) {
            $this->error('error_pages table is missing. Run module migrations first.');

            return self::FAILURE;
        }

        if (! Schema::hasTable((new PageLayout)->getTable())) {
            $this->error('CMS page layouts table is missing. Enable the Cms module and migrate first.');

            return self::FAILURE;
        }

        [$layoutSlug, $layoutBuilderId] = $this->resolveLayoutBinding($pageLayoutResolver);

        foreach (ErrorPageDefaults::pages() as $row) {
            $existing = ErrorPage::query()->where('error_code', $row['error_code'])->first();

            if ($existing !== null && ! $this->option('force')) {
                $this->line("Skip ErrorPage {$row['error_code']} (exists #{$existing->getKey()})");

                continue;
            }

            $payload = [
                'name' => $row['name'],
                'error_code' => $row['error_code'],
                'published' => $row['published'],
            ];

            if ($existing !== null) {
                $existing->fill($payload)->save();
                $this->info("Updated ErrorPage {$row['error_code']} (#{$existing->getKey()})");
            } else {
                $created = ErrorPage::query()->create($payload);
                $this->info("Created ErrorPage {$row['error_code']} (#{$created->getKey()})");
            }
        }

        $pageLayout = PageLayout::query()
            ->where('target_model_class', ErrorPage::class)
            ->first();

        $pageLayoutPayload = [
            'target_model_class' => ErrorPage::class,
            'admin_label' => 'Error Pages',
            'enabled' => true,
            'sort_order' => 900,
            'layout_builder_id' => $layoutBuilderId,
            'blade_source' => 'filesystem',
            'blade_view_name' => ErrorPageDefaults::builtinViewName('404'),
        ];

        if ($pageLayout === null) {
            $pageLayout = PageLayout::query()->create($pageLayoutPayload);
            $this->info("Created PageLayout #{$pageLayout->getKey()} for ErrorPage");
        } else {
            $pageLayout->fill([
                'enabled' => true,
                'layout_builder_id' => $layoutBuilderId ?? $pageLayout->layout_builder_id,
                'blade_source' => $pageLayout->blade_source ?: 'filesystem',
                'blade_view_name' => $pageLayout->blade_view_name ?: ErrorPageDefaults::builtinViewName('404'),
                'admin_label' => $pageLayout->admin_label ?: 'Error Pages',
            ])->save();
            $this->info("Updated PageLayout #{$pageLayout->getKey()} for ErrorPage");
        }

        $this->info('Error page defaults are ready.');

        return self::SUCCESS;
    }

    /**
     * @return array{0: string, 1: int|null}
     */
    private function resolveLayoutBinding(CmsPageLayoutResolver $pageLayoutResolver): array
    {
        $layoutSlug = trim((string) ($this->option('layout-slug') ?? ''));

        if ($layoutSlug === '') {
            $layoutSlug = ErrorPageDefaults::configuredLayoutSlug();
        }

        if ($layoutSlug === '') {
            $defaultBuilder = $pageLayoutResolver->defaultLayoutBuilder();
            if ($defaultBuilder !== null) {
                $layoutSlug = trim((string) ($defaultBuilder->slug ?? ''));
                $id = (int) $defaultBuilder->getKey();
                $this->line(
                    'Using CmsPageLayoutResolver::defaultLayoutBuilder()'
                    . ($layoutSlug !== '' ? " [{$layoutSlug}]" : " #{$id}")
                    . '.'
                );

                return [$layoutSlug, $id];
            }

            $this->warn(
                'No --layout-slug, cms_layout_builder.default_layout_slug, or default LayoutBuilder — '
                . 'PageLayout will be created without layout_builder_id.'
            );

            return ['', null];
        }

        $layoutBuilderId = LayoutBuilder::query()->where('slug', $layoutSlug)->value('id');
        $layoutBuilderId = $layoutBuilderId !== null ? (int) $layoutBuilderId : null;

        if ($layoutBuilderId === null) {
            $this->warn("LayoutBuilder slug [{$layoutSlug}] not found — PageLayout will be created without layout_builder_id.");
        }

        return [$layoutSlug, $layoutBuilderId];
    }
}
