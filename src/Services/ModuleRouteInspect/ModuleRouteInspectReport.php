<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\ModuleRouteInspect;

/**
 * Aggregate inspect report for one or more module routes.
 */
final class ModuleRouteInspectReport
{
    /**
     * @param  list<ModuleRouteInspectEntry>  $entries
     */
    public function __construct(
        public readonly array $entries,
    ) {
    }

    /**
     * @param  list<string>  $featureKeys
     */
    public function filter(bool $findingsOnly = false, array $featureKeys = []): self
    {
        $entries = $this->entries;

        if ($findingsOnly) {
            $entries = array_values(array_filter(
                $entries,
                static fn (ModuleRouteInspectEntry $entry): bool => $entry->hasFindings()
            ));
        }

        if ($featureKeys !== []) {
            $wanted = array_fill_keys($featureKeys, true);
            $entries = array_values(array_filter(
                $entries,
                static function (ModuleRouteInspectEntry $entry) use ($wanted): bool {
                    foreach ($entry->presentFeatureKeys() as $key) {
                        if (isset($wanted[$key])) {
                            return true;
                        }
                    }

                    return false;
                }
            ));
        }

        return new self($entries);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (ModuleRouteInspectEntry $entry): array => $entry->toArray(),
            $this->entries
        );
    }

    public function isEmpty(): bool
    {
        return $this->entries === [];
    }

    public function findingCount(): int
    {
        $count = 0;

        foreach ($this->entries as $entry) {
            $count += count($entry->findings);
        }

        return $count;
    }
}
