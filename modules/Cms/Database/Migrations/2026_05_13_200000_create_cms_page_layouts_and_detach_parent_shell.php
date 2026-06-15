<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Cms\Support\LayoutSegmentAppends;

/**
 * Introduces {@code cms_page_layouts} (per routed model class) and moves shell binding from
 * {@code cms_parent_segment_bindings} into that table, then removes the legacy columns from bindings.
 *
 * Replaces the former split chain (add layout_builder_id → add layout_segment_appends → migrate → blade columns → drop appends)
 * so {@code php artisan migrate} builds one coherent feature from {@see 2026_05_12_132301_create_layout_builders_table}.
 */
return new class extends Migration
{
    public function up(): void
    {
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        $pageLayouts = modularousConfig('tables.cms_page_layouts', 'um_cms_page_layouts');
        $bindings = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');
        $this->ensureBindingShellColumns($bindings);

        if (! Schema::hasTable($pageLayouts)) {
            Schema::create($pageLayouts, function (Blueprint $blueprint) use ($layoutBuildersTable): void {
                $blueprint->id();
                $blueprint->string('target_model_class', 512)->unique();
                $blueprint->string('admin_label')->nullable();
                $blueprint->boolean('enabled')->default(true);
                $blueprint->unsignedInteger('sort_order')->default(0);
                $blueprint->foreignId('layout_builder_id')
                    ->nullable()
                    ->constrained($layoutBuildersTable)
                    ->nullOnDelete();
                $blueprint->string('blade_source', 24)->default('db');
                $blueprint->json('blade_segments')->nullable();
                $blueprint->string('blade_view_name', 512)->nullable();

                $blueprint->timestamps();

                createDefaultExtraTableFields($blueprint, true, false);
            });
        }

        if (Schema::hasTable($bindings) && Schema::hasTable($pageLayouts)) {
            $hasLb = Schema::hasColumn($bindings, 'layout_builder_id');
            $hasApp = Schema::hasColumn($bindings, 'layout_segment_appends');

            if ($hasLb || $hasApp) {
                $rows = DB::table($bindings)
                    ->orderByRaw("CASE WHEN COALESCE(locale, '') = '' THEN 0 ELSE 1 END ASC")
                    ->orderBy('id')
                    ->get();

                $seen = [];

                foreach ($rows as $row) {
                    $class = (string) ($row->target_model_class ?? '');
                    if ($class === '' || isset($seen[$class])) {
                        continue;
                    }

                    $lbId = ($hasLb && $row->layout_builder_id !== null && $row->layout_builder_id !== '')
                        ? (int) $row->layout_builder_id
                        : null;

                    $jsonBladeSegments = null;
                    if ($hasApp && $row->layout_segment_appends !== null && $row->layout_segment_appends !== '') {
                        $decoded = json_decode((string) $row->layout_segment_appends, true);
                        $normalized = LayoutSegmentAppends::normalize(is_array($decoded) ? $decoded : []);
                        if (trim($normalized['head'] . $normalized['body'] . $normalized['footer']) !== '') {
                            $jsonBladeSegments = json_encode($normalized);
                        }
                    }

                    $hasPayload = ($lbId !== null) || ($jsonBladeSegments !== null);
                    if (! $hasPayload) {
                        continue;
                    }

                    if (DB::table($pageLayouts)->where('target_model_class', $class)->exists()) {
                        $seen[$class] = true;

                        continue;
                    }

                    DB::table($pageLayouts)->insert([
                        'target_model_class' => $class,
                        'admin_label' => null,
                        'enabled' => (bool) ($row->enabled ?? true),
                        'sort_order' => (int) ($row->sort_order ?? 0),
                        'layout_builder_id' => $lbId,
                        'blade_source' => 'db',
                        'blade_segments' => $jsonBladeSegments,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $seen[$class] = true;
                }
            }
        }

        $this->dropBindingShellColumns($bindings);
    }

    public function down(): void
    {
        $layoutBuildersTable = modularousConfig('tables.cms_layout_builders', 'um_cms_layout_builders');
        $pageLayouts = modularousConfig('tables.cms_page_layouts', 'um_cms_page_layouts');
        $bindings = modularousConfig('tables.cms_parent_segment_bindings', 'um_cms_parent_segment_bindings');

        Schema::dropIfExists($pageLayouts);

        if (Schema::hasTable($bindings) && ! Schema::hasColumn($bindings, 'layout_builder_id')) {
            Schema::table($bindings, function (Blueprint $blueprint) use ($layoutBuildersTable): void {
                $blueprint->foreignId('layout_builder_id')
                    ->nullable()
                    ->after('sort_order')
                    ->constrained($layoutBuildersTable)
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable($bindings) && ! Schema::hasColumn($bindings, 'layout_segment_appends')) {
            Schema::table($bindings, function (Blueprint $blueprint): void {
                $blueprint->json('layout_segment_appends')->nullable()->after('layout_builder_id');
            });
        }
    }

    private function ensureBindingShellColumns(string $bindings): void
    {
        if (! Schema::hasTable($bindings)) {
            return;
        }

        if (! Schema::hasColumn($bindings, 'layout_builder_id')) {
            Schema::table($bindings, function (Blueprint $blueprint): void {
                $blueprint->foreignId('layout_builder_id')
                    ->nullable()
                    ->after('sort_order')
                    ->constrained('layout_builders')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn($bindings, 'layout_segment_appends')) {
            Schema::table($bindings, function (Blueprint $blueprint): void {
                $blueprint->json('layout_segment_appends')->nullable()->after('layout_builder_id');
            });
        }
    }

    private function dropBindingShellColumns(string $bindings): void
    {
        if (! Schema::hasTable($bindings)) {
            return;
        }

        if (Schema::hasColumn($bindings, 'layout_segment_appends')) {
            Schema::table($bindings, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('layout_segment_appends');
            });
        }

        if (Schema::hasColumn($bindings, 'layout_builder_id')) {
            Schema::table($bindings, function (Blueprint $blueprint): void {
                $blueprint->dropConstrainedForeignId('layout_builder_id');
            });
        }
    }
};
