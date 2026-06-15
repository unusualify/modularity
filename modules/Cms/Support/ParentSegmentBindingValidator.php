<?php

namespace Modules\Cms\Support;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Modules\Cms\Entities\ParentSegment;

/**
 * Cross-row rules for {@see ParentSegment} (locale scope + overlapping targets).
 *
 * Homepage / locale-root URLs share an empty trimmed {@code normalized_prefix}; at most one
 * enabled binding may claim that scope per overlapping locale wildcard rules.
 */
final class ParentSegmentBindingValidator
{
    /**
     * Two locale columns overlap when either is blank (meaning “all locales”) or both match.
     */
    public static function localeScopesOverlap(?string $localeA, ?string $localeB): bool
    {
        $a = trim((string) ($localeA ?? ''));
        $b = trim((string) ($localeB ?? ''));

        return $a === '' || $b === '' || $a === $b;
    }

    public static function isWhitespaceOnlyEmptyPrefix(?string $normalizedPrefix): bool
    {
        return trim((string) ($normalizedPrefix ?? '')) === '';
    }

    /**
     * Ensures another enabled CMS model binding does not already use an empty homepage prefix on the same locale scope.
     *
     * @throws ValidationException When a conflicting binding exists (field {@code normalized_prefix}).
     */
    public static function assertExclusiveEmptyPrefixAcrossTargetsIfEnabled(
        bool $enabled,
        string $targetModelClass,
        ?string $localeScope,
        ?string $normalizedPrefix,
        ?int $exceptId = null,
    ): void {
        if (! $enabled || ! self::isWhitespaceOnlyEmptyPrefix($normalizedPrefix)) {
            return;
        }

        $localeScope = trim((string) ($localeScope ?? ''));

        $conflicting = self::enabledBindingsWithEffectivelyEmptyPrefix($exceptId)
            ->first(function (ParentSegment $row) use ($targetModelClass, $localeScope): bool {
                return (string) $row->target_model_class !== (string) $targetModelClass
                    && static::localeScopesOverlap($localeScope, (string) ($row->locale ?? ''));
            });

        if ($conflicting !== null) {
            throw ValidationException::withMessages([
                'normalized_prefix' => [
                    __('CMS already has a homepage binding (empty path prefix) for this locale scope on another module model (ID :id).', [
                        'id' => $conflicting->getKey(),
                    ]),
                ],
            ]);
        }
    }

    /** @return Collection<int, ParentSegment> */
    public static function enabledBindingsWithEffectivelyEmptyPrefix(?int $exceptId = null): Collection
    {
        $query = ParentSegment::query()
            ->where('enabled', true)
            ->when($exceptId !== null, fn ($q) => $q->whereKeyNot($exceptId));

        return $query->get()->filter(
            fn (ParentSegment $row): bool => self::isWhitespaceOnlyEmptyPrefix((string) ($row->normalized_prefix ?? ''))
        )->values();
    }
}
