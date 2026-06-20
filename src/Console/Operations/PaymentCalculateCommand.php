<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Console\Operations;

use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Entities\Traits\HasPayment;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;
use Unusualify\Modularous\Services\PaymentCalculationInput;
use Unusualify\Modularous\Services\PaymentCalculationResult;
use Unusualify\Modularous\Services\PaymentCalculationService;

class PaymentCalculateCommand extends Command
{
    protected $signature = 'modularous:payment:calculate
                            {id : Price ID, or model record ID when --module and --route are set}
                            {--user= : Paying user ID}
                            {--email= : Paying user email}
                            {--payment-service= : PaymentService ID or key}
                            {--module= : Module name (requires --route)}
                            {--route= : Route name (requires --module)}
                            {--vat-rate= : VatRate ID or slug (required with --vat-rate-from=country|currency)}
                            {--vat-rate-from= : default|country|currency — omit for automatic pay() flow}
                            {--currency= : PaymentCurrency ID or ISO 4217 code}
                            {--exchange-rate= : Manual exchange rate override}
                            {--paid-at= : Payment datetime (Y-m-d H:i:s)}
                            {--locale= : Locale for modularous payload}
                            {--dry-run : Preview calculation and SQL without creating payment}
                            {--force : Continue even if price already has a completed payment}';

    protected $description = 'Calculate payment totals (PriceController::pay logic) and optionally create a COMPLETED payment record';

    public function handle(PaymentCalculationService $calculationService): int
    {
        $user = $this->resolveUser();
        if (! $user) {
            return self::FAILURE;
        }

        $paymentService = $this->resolvePaymentService($this->option('payment-service'));
        if (! $paymentService) {
            return self::FAILURE;
        }

        $price = $this->resolvePrice($this->argument('id'));
        if (! $price) {
            return self::FAILURE;
        }

        if (! $this->option('force') && $price->payment && $price->payment->status === PaymentStatus::COMPLETED) {
            $this->components->error('This price already has a completed payment. Use --force to continue.');

            return self::FAILURE;
        }

        $vatRateFrom = $this->option('vat-rate-from');
        if ($vatRateFrom !== null && ! in_array($vatRateFrom, ['default', 'country', 'currency'], true)) {
            $this->components->error('--vat-rate-from must be one of: default, country, currency.');

            return self::FAILURE;
        }

        $vatRateOverride = null;
        if (in_array($vatRateFrom, ['country', 'currency'], true)) {
            $vatRateOverride = $this->resolveVatRate($this->option('vat-rate'));
            if (! $vatRateOverride) {
                $this->components->error('--vat-rate is required when --vat-rate-from is country or currency.');

                return self::FAILURE;
            }
        }

        $targetCurrency = $this->resolveTargetCurrencyIso4217($price, $this->option('currency'));
        if ($targetCurrency === null) {
            return self::FAILURE;
        }

        $exchangeRate = $this->option('exchange-rate');
        $exchangeRateOverride = $exchangeRate !== null && $exchangeRate !== ''
            ? (float) $exchangeRate
            : null;

        $paidAt = $this->option('paid-at') ?: null;
        if ($paidAt !== null && ! $this->isValidDateTime($paidAt)) {
            $this->components->error('--paid-at must be a valid datetime in Y-m-d H:i:s format.');

            return self::FAILURE;
        }

        $previousUser = Auth::guard('modularous')->user();
        Auth::guard('modularous')->setUser($user);

        try {
            $result = $calculationService->calculate(new PaymentCalculationInput(
                price: $price,
                user: $user,
                paymentService: $paymentService,
                targetCurrencyIso4217: $targetCurrency,
                exchangeRateOverride: $exchangeRateOverride,
                vatRateFromOverride: $vatRateFrom,
                vatRateOverride: $vatRateOverride,
                locale: $this->option('locale') ?: null,
                paidAt: $paidAt,
                previousUrl: null,
            ));
        } catch (\InvalidArgumentException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            $this->restoreAuthUser($previousUser);
        }

        $this->renderDryRunOutput($result);

        if ($this->option('dry-run')) {
            $this->newLine();
            $this->components->info('Dry run complete. No payment record was created.');

            return self::SUCCESS;
        }

        $payment = $price->updateOrNewPayment($result->paymentPayload);
        $this->newLine();
        $this->components->info(sprintf('Payment #%d created with status COMPLETED.', $payment->id));

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $userId = $this->option('user');
        $email = $this->option('email');

        if (! $userId && ! $email) {
            $this->components->error('Either --user or --email is required.');

            return null;
        }

        $user = $userId
            ? User::query()->find($userId)
            : User::query()->where('email', $email)->first();

        if (! $user) {
            $this->components->error('User not found.');

            return null;
        }

        return $user;
    }

    private function resolvePaymentService(?string $value): ?PaymentService
    {
        if (! $value) {
            $this->components->error('--payment-service is required.');

            return null;
        }

        $service = PaymentService::query()
            ->where('id', $value)
            ->orWhere('key', $value)
            ->first();

        if (! $service) {
            $this->components->error(sprintf('PaymentService not found for: %s', $value));

            return null;
        }

        return $service;
    }

    private function resolveVatRate(?string $value): ?VatRate
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return VatRate::query()->find($value);
        }

        return VatRate::query()->where('slug', $value)->first();
    }

    private function resolvePrice(string $id): ?Price
    {
        $module = $this->option('module');
        $route = $this->option('route');

        if (($module && ! $route) || (! $module && $route)) {
            $this->components->error('--module and --route must be provided together.');

            return null;
        }

        if ($module && $route) {
            return $this->resolvePriceFromModule($module, $route, $id);
        }

        $price = Price::query()
            ->with(['currency', 'vatRate', 'paymentCurrency', 'payment'])
            ->find($id);

        if (! $price) {
            $this->components->error(sprintf('Price not found for ID: %s', $id));

            return null;
        }

        return $price;
    }

    private function resolvePriceFromModule(string $moduleName, string $routeName, string $recordId): ?Price
    {
        $module = Modularous::findOrFail($moduleName);
        $modelClass = $module->getModel($routeName, asClass: false);

        if (! in_array(HasPayment::class, class_uses_recursive($modelClass), true)) {
            $this->components->error(sprintf('Model %s does not use HasPayment trait.', $modelClass));

            return null;
        }

        $model = $modelClass::query()->find($recordId);
        if (! $model) {
            $this->components->error(sprintf('Record not found for ID: %s', $recordId));

            return null;
        }

        $price = $model->payablePrice()
            ->with(['currency', 'vatRate', 'paymentCurrency', 'payment'])
            ->first();

        if (! $price) {
            $this->components->warn('No payable price found for this record (may already be paid or provisioned).');

            return null;
        }

        return $price;
    }

    private function resolveTargetCurrencyIso4217(Price $price, ?string $currencyOption): ?string
    {
        if (! $currencyOption) {
            return $price->currency->iso_4217;
        }

        $currency = is_numeric($currencyOption)
            ? PaymentCurrency::query()->find($currencyOption)
            : PaymentCurrency::query()->where('iso_4217', strtoupper($currencyOption))->first();

        if (! $currency) {
            $this->components->error(sprintf('PaymentCurrency not found for: %s', $currencyOption));

            return null;
        }

        return $currency->iso_4217;
    }

    private function isValidDateTime(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        return $date !== false && $date->format('Y-m-d H:i:s') === $value;
    }

    private function restoreAuthUser(?Authenticatable $previousUser): void
    {
        if ($previousUser) {
            Auth::guard('modularous')->setUser($previousUser);

            return;
        }

        Auth::guard('modularous')->logout();
    }

    private function renderDryRunOutput(PaymentCalculationResult $result): void
    {
        $this->newLine();
        $this->components->info('Payment Calculation Summary');
        $this->table(['Field', 'Value'], collect($result->summaryRows())
            ->map(fn ($value, $key) => [$key, is_bool($value) ? ($value ? 'true' : 'false') : (string) ($value ?? '')])
            ->values()
            ->all());

        $this->newLine();
        $this->components->info('Modularous Payload (parameters.modularous)');
        $this->line(json_encode(['modularous' => $result->modularousPayload], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $this->newLine();
        $this->components->info('SQL — payments');
        $this->line($result->toInsertSql());

        $this->newLine();
        $this->components->warn('creator_record is created automatically by HasCreator when the payment is saved via Eloquent.');
        $this->components->info('SQL — creator_record (example assuming new payment id=123)');
        $this->line($result->toCreatorRecordInsertSql(123));
    }
}
