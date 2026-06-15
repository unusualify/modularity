<?php

namespace Modules\Cms\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Entities\Concerns\HasPageLayout;
use Modules\Cms\Entities\LayoutBuilder;
use Modules\Cms\Entities\PageLayout;
use Modules\Cms\Entities\ParentSegment;

/**
 * Resolves locale-agnostic {@see PageLayout} rows keyed by routed model FQCN (see {@see HasPageLayout}).
 *
 * Prefer this over tying shells to {@see ParentSegment} bindings.
 */
final class CmsPageLayoutResolver
{
    public function enabled(): bool
    {
        return (bool) modularousConfig('cms_page_layouts.enabled', true);
    }

    public function tablesReady(): bool
    {
        return Schema::hasTable((new PageLayout)->getTable());
    }

    /**
     * Enabled row for this model class (morph-alias aware), if any.
     */
    public function pageLayoutForModelClass(string $targetClass): ?PageLayout
    {
        if (! $this->enabled() || ! $this->tablesReady()) {
            return null;
        }

        $aliases = $this->targetModelAliases($targetClass);

        return PageLayout::query()
            ->whereIn('target_model_class', $aliases)
            ->where('enabled', true)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * PageLayout row for this model class regardless of {@code enabled} (morph-alias aware), if any.
     */
    public function pageLayoutBindingForModelClass(string $targetClass): ?PageLayout
    {
        if (! $this->enabled() || ! $this->tablesReady()) {
            return null;
        }

        $aliases = $this->targetModelAliases($targetClass);

        return PageLayout::query()
            ->whereIn('target_model_class', $aliases)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * {@see LayoutBuilder} shell for this model: {@code layout_builder_id} from any PageLayout binding (enabled or not),
     * then {@see defaultLayoutBuilder()}.
     */
    public function layoutBuilderShellForModelClass(string $targetClass): ?LayoutBuilder
    {
        $layoutBuilderId = $this->layoutBuilderIdForModelClass($targetClass);
        if ($layoutBuilderId !== null) {
            $layout = LayoutBuilder::query()
                ->whereKey($layoutBuilderId)
                ->with(['styleSheet'])
                ->first();

            if ($layout !== null) {
                return $layout;
            }
        }

        return $this->defaultLayoutBuilder();
    }

    /**
     * Configured default shell when no PageLayout binding defines {@code layout_builder_id}.
     */
    public function defaultLayoutBuilder(): ?LayoutBuilder
    {
        $configuredId = modularousConfig('cms_page_layouts.default_layout_builder_id');
        if ($configuredId !== null && $configuredId !== '') {
            $layout = LayoutBuilder::query()
                ->whereKey((int) $configuredId)
                ->with(['styleSheet'])
                ->first();

            if ($layout !== null) {
                return $layout;
            }
        }

        $slug = trim((string) modularousConfig('cms_layout_builder.default_layout_slug', ''));
        if ($slug === '') {
            return null;
        }

        return LayoutBuilder::query()
            ->where('slug', $slug)
            ->with(['styleSheet'])
            ->first();
    }

    private function layoutBuilderIdForModelClass(string $targetClass): ?int
    {
        $binding = $this->pageLayoutBindingForModelClass($targetClass);
        if ($binding === null || $binding->layout_builder_id === null) {
            return null;
        }

        return (int) $binding->layout_builder_id;
    }

    /**
     * @return list<class-string|string>
     */
    private function targetModelAliases(string $targetClass): array
    {
        $aliases = [$targetClass];
        if (class_exists($targetClass) && is_a($targetClass, Model::class, true)) {
            try {
                /** @phpstan-ignore-next-line */
                $aliases[] = (new $targetClass)->getMorphClass();
            } catch (\Throwable) {
            }
        }

        /** @var list<class-string|string> */
        return array_values(array_unique(array_filter(array_map('strval', $aliases))));
    }
}
