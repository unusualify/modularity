<?php

namespace Unusualify\Modularous\Entities\Traits\Core;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\SystemUser\Entities\Capability;

trait HasCapabilities
{
    public static function bootHasCapabilities(): void
    {
        if (! modularousConfig('security.step_up.enabled', false)) {
            return;
        }

        static::addGlobalScope('capabilities', function (Builder $builder) {
            $model = $builder->getModel();
            $usersTable = $model->getTable();
            $capabilitiesTable = modularousConfig('tables.capabilities', 'um_capabilities');
            $roleCapabilityTable = modularousConfig('tables.role_capability', 'um_role_capability');
            $modelHasRolesTable = config('permission.table_names.model_has_roles', 'sp_model_has_roles');
            $modelMorphKey = config('permission.column_names.model_morph_key', 'model_id');

            if (! class_exists(Capability::class)
                || ! Schema::hasTable($capabilitiesTable)
                || ! Schema::hasTable($roleCapabilityTable)
                || ! Schema::hasTable($modelHasRolesTable)) {
                return;
            }

            $capabilitiesSubQuery = DB::table("{$capabilitiesTable} as capabilities")
                ->selectRaw(
                    "COALESCE(CONCAT('[', GROUP_CONCAT(DISTINCT JSON_QUOTE(capabilities.name) ORDER BY capabilities.name SEPARATOR ','), ']'), '[]')"
                )
                ->join("{$roleCapabilityTable} as role_capability", 'role_capability.capability_id', '=', 'capabilities.id')
                ->join("{$modelHasRolesTable} as model_has_roles", 'model_has_roles.role_id', '=', 'role_capability.role_id')
                ->whereColumn("model_has_roles.{$modelMorphKey}", "{$usersTable}.id")
                ->where('model_has_roles.model_type', $model::class)
                ->where('capabilities.published', true);

            if ($builder->getQuery()->columns === null) {
                $builder->select("{$usersTable}.*");
            }

            $builder->addSelect(['capabilities_payload' => $capabilitiesSubQuery]);
        });
    }

    public function initializeHasCapabilities(): void
    {
        if (! modularousConfig('security.step_up.enabled', false)) {
            return;
        }

        $this->append(['capabilities']);
    }

    protected function capabilities(): Attribute
    {
        return Attribute::make(
            get: function ($value, array $attributes) {
                $payload = $attributes['capabilities_payload'] ?? '[]';
                $decoded = json_decode((string) $payload, true);

                if (! is_array($decoded)) {
                    return [];
                }

                return array_values(array_unique(array_filter($decoded, fn ($capability) => is_string($capability) && $capability !== '')));
            }
        );
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? [], true);
    }
}
