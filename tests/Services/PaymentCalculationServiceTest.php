<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Tests\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\Price;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\CurrencyExchange;
use Unusualify\Modularous\Services\PaymentCalculationInput;
use Unusualify\Modularous\Services\PaymentCalculationService;
use Unusualify\Modularous\Tests\Repositories\RepositorySources;
use Unusualify\Modularous\Tests\RepositoryTestCase;

class PaymentCalculationServiceTest extends RepositoryTestCase
{
    use RefreshDatabase, RepositorySources;

    private PaymentCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadRepositorySources();
        $this->seedPaymentTables();

        Config::set('modularous.use_country_based_vat_rates', false);
        Config::set('modularous.include_transaction_fee', false);

        $this->service = App::make(PaymentCalculationService::class);
    }

    public function test_same_currency_uses_price_total_without_company_vat(): void
    {
        $user = $this->createUser();
        $price = $this->createPrice(rawAmount: 40000, vatRateId: 1);
        $paymentService = $this->createPaymentService();

        $result = $this->service->calculate(new PaymentCalculationInput(
            price: $price->load(['currency', 'vatRate', 'paymentCurrency']),
            user: $user,
            paymentService: $paymentService,
        ));

        $this->assertSame('default', $result->vatRateFrom);
        $this->assertFalse($result->isCompanyBasedVatRate);
        $this->assertFalse($result->converted);
        $this->assertSame(48000, $result->amount);
        $this->assertSame($user->email, $result->paymentPayload['email']);
        $this->assertSame($user->id, $result->paymentPayload['custom_creator_id']);
        $this->assertSame($user::class, $result->paymentPayload['custom_creator_type']);
        $this->assertSame('modularous', $result->paymentPayload['custom_guard_name']);
    }

    public function test_country_vat_override_with_manual_exchange_rate(): void
    {
        $user = $this->createUser();
        $price = $this->createPrice(rawAmount: 40000, vatRateId: 1);
        $paymentService = $this->createPaymentService();
        $tryCurrency = PaymentCurrency::query()->where('iso_4217', 'TRY')->first();
        $countryVat = VatRate::query()->create([
            'name' => 'TR-Standard VAT',
            'slug' => 'tr-standard-vat',
            'rate' => 20,
        ]);

        $result = $this->service->calculate(new PaymentCalculationInput(
            price: $price->load(['currency', 'vatRate', 'paymentCurrency']),
            user: $user,
            paymentService: $paymentService,
            targetCurrencyIso4217: 'TRY',
            exchangeRateOverride: 53.27,
            vatRateFromOverride: 'country',
            vatRateOverride: $countryVat,
            paidAt: '2026-05-13 07:44:50',
        ));

        $this->assertTrue($result->converted);
        $this->assertSame('country', $result->vatRateFrom);
        $this->assertTrue($result->isCompanyBasedVatRate);
        $this->assertSame(2130800, $result->modularousPayload['converted_raw_amount']);
        $this->assertSame(2556960, $result->modularousPayload['converted_total_amount']);
        $this->assertSame(2556960, $result->modularousPayload['company_based_total_amount']);
        $this->assertSame(2556960, $result->amount);
        $this->assertSame('TRY', $result->currency->iso_4217);
        $this->assertSame($tryCurrency->id, $result->currency->id);
    }

    public function test_transaction_fee_is_applied_when_enabled(): void
    {
        Config::set('modularous.include_transaction_fee', true);

        $user = $this->createUser();
        $price = $this->createPrice(rawAmount: 10000, vatRateId: 1);
        $paymentService = $this->createPaymentService(transactionFeePercentage: 3.5);

        $result = $this->service->calculate(new PaymentCalculationInput(
            price: $price->load(['currency', 'vatRate', 'paymentCurrency']),
            user: $user,
            paymentService: $paymentService,
        ));

        $this->assertTrue($result->hasTransactionFee);
        $this->assertSame(12000, $result->totalAmountWithoutTransactionFee);
        $this->assertSame(420.0, $result->transactionFeeAmount);
        $this->assertSame(12420, $result->amount);
        $this->assertTrue($result->modularousPayload['transaction_fee_exists']);
    }

    public function test_to_insert_sql_contains_expected_columns(): void
    {
        $user = $this->createUser();
        $price = $this->createPrice(rawAmount: 10000, vatRateId: 1);
        $paymentService = $this->createPaymentService();

        $result = $this->service->calculate(new PaymentCalculationInput(
            price: $price->load(['currency', 'vatRate', 'paymentCurrency']),
            user: $user,
            paymentService: $paymentService,
            paidAt: '2026-01-01 12:00:00',
        ));

        $sql = $result->toInsertSql();

        $this->assertStringContainsString('INSERT INTO `up_payments`', $sql);
        $this->assertStringContainsString('payment_service_id', $sql);
        $this->assertStringContainsString($user->email, $sql);
        $this->assertSame($user->id, $result->paymentPayload['custom_creator_id']);
        $this->assertStringContainsString('COMPLETED', $sql);
        $this->assertStringContainsString('2026-01-01 12:00:00', $sql);

        $creatorSql = $result->toCreatorRecordInsertSql(99);
        $this->assertStringContainsString('SystemPayment', $creatorSql);
        $this->assertStringContainsString((string) $user->id, $creatorSql);
    }

    public function test_currency_exchange_facade_is_used_when_no_manual_rate(): void
    {
        CurrencyExchange::shouldReceive('convertTo')
            ->once()
            ->andReturn(2280400);
        CurrencyExchange::shouldReceive('getExchangeRate')
            ->once()
            ->with('TRY')
            ->andReturn(57.01);

        $user = $this->createUser();
        $price = $this->createPrice(rawAmount: 40000, vatRateId: 1);
        $paymentService = $this->createPaymentService();
        $countryVat = VatRate::query()->create([
            'name' => 'TR-Standard VAT',
            'slug' => 'tr-standard-vat-facade',
            'rate' => 20,
        ]);

        $result = $this->service->calculate(new PaymentCalculationInput(
            price: $price->load(['currency', 'vatRate', 'paymentCurrency']),
            user: $user,
            paymentService: $paymentService,
            targetCurrencyIso4217: 'TRY',
            vatRateFromOverride: 'country',
            vatRateOverride: $countryVat,
        ));

        $this->assertSame(2280400, $result->modularousPayload['converted_raw_amount']);
        $this->assertSame(57.01, $result->exchangeRate);
    }

    private function seedPaymentTables(): void
    {
        Schema::create('payment_services', function (Blueprint $table) {
            createDefaultTableFields($table);
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->decimal('transaction_fee_percentage', 5, 2)->default(0.00);
            $table->boolean('is_external')->default(false);
            $table->boolean('is_internal')->default(false);
            $table->string('button_style')->nullable();
            createDefaultExtraTableFields($table);
        });

        Schema::create('payment_currency_payment_service', function (Blueprint $table) {
            createDefaultRelationshipTableFields(
                $table,
                'payment_currency',
                'payment_service',
                config('priceable.tables.currencies', 'unfy_currencies')
            );
        });

        Schema::table(config('priceable.tables.currencies', 'unfy_currencies'), function (Blueprint $table) {
            $table->unsignedBigInteger('payment_service_id')->nullable();
        });

        PaymentCurrency::query()->firstOrCreate(
            ['iso_4217' => 'TRY'],
            [
                'name' => 'Turkish Lira',
                'symbol' => '₺',
                'iso_4217_number' => 949,
            ]
        );

        VatRate::query()->firstOrCreate(
            ['slug' => 'zero-vat'],
            [
                'name' => 'Zero VAT',
                'rate' => 0,
            ]
        );
    }

    private function createUser(): User
    {
        return User::query()->create([
            'name' => 'Payment',
            'surname' => 'Tester',
            'email' => 'payment-tester@example.com',
            'password' => bcrypt('secret'),
            'published' => true,
            'language' => 1,
        ]);
    }

    private function createPrice(int $rawAmount, int $vatRateId): Price
    {
        $vatRate = VatRate::query()->findOrFail($vatRateId);
        $currency = Currency::query()->where('iso_4217', 'EUR')->firstOrFail();
        $priceValue = $rawAmount / 100;

        return Price::query()->create([
            'priceable_type' => 'test',
            'priceable_id' => 1,
            'role' => 'payment',
            'price_type_id' => 1,
            'currency_id' => $currency->id,
            'vat_rate_id' => $vatRate->id,
            'price_value' => $priceValue,
            'discount_percentage' => 0,
        ]);
    }

    private function createPaymentService(float $transactionFeePercentage = 0.0): PaymentService
    {
        return PaymentService::query()->create([
            'name' => 'Test Gateway',
            'key' => 'test-gateway-' . uniqid(),
            'published' => true,
            'transaction_fee_percentage' => $transactionFeePercentage,
            'is_external' => false,
            'is_internal' => true,
        ]);
    }
}
