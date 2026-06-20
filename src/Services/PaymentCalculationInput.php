<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services;

use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularous\Entities\User;

final class PaymentCalculationInput
{
    public function __construct(
        public readonly Price $price,
        public readonly User $user,
        public readonly PaymentService $paymentService,
        public readonly ?string $targetCurrencyIso4217 = null,
        public readonly ?float $exchangeRateOverride = null,
        public readonly ?string $vatRateFromOverride = null,
        public readonly ?VatRate $vatRateOverride = null,
        public readonly ?string $locale = null,
        public readonly ?string $paidAt = null,
        public readonly ?string $previousUrl = null,
    ) {}
}
