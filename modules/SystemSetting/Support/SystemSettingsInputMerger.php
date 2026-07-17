<?php

declare(strict_types=1);

namespace Modules\SystemSetting\Support;

/**
 * Merges host {@see modularous.system_settings} overrides into module route inputs.
 */
class SystemSettingsInputMerger
{
    /**
     * @param array<int, array<string, mixed>|string> $defaultInputs
     * @return array<int, array<string, mixed>>
     */
    public function merge(array $defaultInputs): array
    {
        $config = (array) modularousConfig('system_settings', []);
        $inputs = $this->expandAliasesInList($defaultInputs);
        $inputs = $this->applyOverrides($inputs, (array) ($config['input_overrides'] ?? []));
        $inputs = array_merge($inputs, $this->expandAliasesInList((array) ($config['extra_inputs'] ?? [])));

        $hidden = array_values(array_filter((array) ($config['hidden_inputs'] ?? [])));

        if ($hidden === []) {
            return $inputs;
        }

        return array_values(array_filter(
            $inputs,
            fn (array $input) => ! in_array((string) ($input['name'] ?? ''), $hidden, true)
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $inputs
     * @param array<string, array<string, mixed>> $overrides
     * @return array<int, array<string, mixed>>
     */
    protected function applyOverrides(array $inputs, array $overrides): array
    {
        if ($overrides === []) {
            return $inputs;
        }

        return array_map(function (array $input) use ($overrides): array {
            $name = (string) ($input['name'] ?? '');

            if ($name === '' || ! isset($overrides[$name])) {
                return $input;
            }

            return array_replace_recursive($input, $overrides[$name]);
        }, $inputs);
    }

    /**
     * @param array<int, array<string, mixed>|string> $inputs
     * @return array<int, array<string, mixed>>
     */
    protected function expandAliasesInList(array $inputs): array
    {
        $resolved = [];

        foreach ($inputs as $input) {
            if (is_string($input)) {
                $definition = modularousConfig('input_types.' . $input);
                if (is_array($definition)) {
                    $resolved[] = $definition;

                    continue;
                }

                $hydrated = function_exists('hydrate_input_type')
                    ? hydrate_input_type(['type' => $input])
                    : null;
                if (is_array($hydrated)) {
                    $resolved[] = $hydrated;
                }

                continue;
            }

            if (is_array($input)) {
                if (isset($input['type']) && is_string($input['type']) && str_starts_with($input['type'], '@')) {
                    $input = function_exists('hydrate_input_type') ? hydrate_input_type($input) : $input;
                }

                $resolved[] = $input;
            }
        }

        return $resolved;
    }
}
