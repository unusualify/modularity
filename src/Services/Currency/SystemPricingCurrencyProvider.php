<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Modules\SystemPricing\Entities\Currency;
use Unusualify\Modularous\Contracts\CurrencyProviderInterface;

/**
 * Currency provider using \Modules\SystemPricing\Entities\Currency.
 * Only used when SystemPricing module is enabled.
 */
class SystemPricingCurrencyProvider implements CurrencyProviderInterface
{
    public function findByIso4217(string $isoCode): ?object
    {
        if (! class_exists(Currency::class)) {
            return null;
        }

        return Cache::remember('currency_by_iso_4217_' . $isoCode, now()->addHours(1), function () use ($isoCode) {
            return Currency::query()
                ->where('iso_4217', mb_strtoupper($isoCode))
                ->first();
        });
    }

    public function findById(int $id): ?object
    {
        if (! class_exists(Currency::class)) {
            return null;
        }

        return Cache::remember('currency_by_id_' . $id, now()->addHours(1), function () use ($id) {
            return Currency::find($id);
        });
    }

    public function getCurrenciesForSelect(): array
    {
        if (! class_exists(Currency::class)) {
            return [];
        }

        return Currency::query()
            ->select(['id', 'symbol as name', 'iso_4217 as iso'])
            ->when(
                modularousConfig('services.currency_exchange.active'),
                fn ($q) => $q->where('iso_4217', mb_strtoupper(modularousConfig('services.currency_exchange.base_currency', 'EUR')))
            )
            ->get()
            ->toArray();
    }

    public function isAvailable(): bool
    {
        return class_exists(Currency::class);
    }
}
