<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services;

use Illuminate\Support\Str;
use Modules\SystemPayment\Entities\PaymentCurrency;
use Modules\SystemPricing\Entities\Currency;
use Modules\SystemPricing\Entities\VatRate;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Facades\CurrencyExchange;
use Unusualify\Modularous\Facades\Modularous;

final class PaymentCalculationService
{
    public function calculate(PaymentCalculationInput $input): PaymentCalculationResult
    {
        $price = $input->price;
        $user = $input->user;
        $paymentService = $input->paymentService;
        $company = $user->company;
        $companyType = $company?->company_type ?? 'system';

        $requestCurrencyIso4217 = $input->targetCurrencyIso4217
            ?? $price->currency->iso_4217;

        $useCountryBasedVatRates = Modularous::shouldUseCountryBasedVatRates();
        $vatRateFromOverride = $input->vatRateFromOverride;

        $rawAmount = $price->discounted_raw_amount;
        $totalAmount = $price->total_amount;

        $vatRateFrom = 'default';
        $isCompanyBasedVatRate = false;
        $companyBasedTotalAmount = $totalAmount;
        $companyBasedVatRateId = null;
        $companyBasedVatRateName = null;
        $companyBasedVatRate = null;
        $companyBasedVatRateMultiplier = null;

        $paymentCurrency = $price->paymentCurrency;
        $currency = $price->currency;
        $isPriceCurrency = true;

        if ($vatRateFromOverride === 'default') {
            if (Str::upper($currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
                $currency = Currency::where('iso_4217', $requestCurrencyIso4217)->first();
                $isPriceCurrency = false;
            }
        } elseif ($vatRateFromOverride === 'country' || $vatRateFromOverride === 'currency') {
            $overrideVatRate = $input->vatRateOverride;
            if (! $overrideVatRate instanceof VatRate) {
                throw new \InvalidArgumentException('--vat-rate is required when --vat-rate-from is country or currency.');
            }

            $vatRateFrom = $vatRateFromOverride;
            $totalAmount = $price->calculateTotalAmount($price->discounted_raw_amount, $overrideVatRate->vat_multiplier);
            $isCompanyBasedVatRate = true;
            $companyBasedTotalAmount = $totalAmount;
            $companyBasedVatRateId = $overrideVatRate->id;
            $companyBasedVatRateName = $overrideVatRate->name;
            $companyBasedVatRate = $overrideVatRate->rate;
            $companyBasedVatRateMultiplier = $overrideVatRate->vat_multiplier;

            if ($useCountryBasedVatRates && Str::upper($paymentCurrency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
                $paymentCurrency = PaymentCurrency::where('iso_4217', $requestCurrencyIso4217)->first();
                $currency = $paymentCurrency;
                $isPriceCurrency = false;
            } elseif (Str::upper($currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
                $currency = Currency::where('iso_4217', $requestCurrencyIso4217)->first();
                $isPriceCurrency = false;
            }
        } elseif ($useCountryBasedVatRates) {
            if (Str::upper($paymentCurrency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
                $paymentCurrency = PaymentCurrency::where('iso_4217', $requestCurrencyIso4217)->first();
                $currency = $paymentCurrency;
                $isPriceCurrency = false;
            }

            if ($paymentCurrency && $paymentCurrency->hasCompanyVatRate()) {
                $paymentCurrency->setCompanyVatRate();
                $companyVatRate = $paymentCurrency->companyVatRate;
                if ($companyVatRate) {
                    $vatRateFrom = $paymentCurrency->isUserCorporateVatRate() ? 'country' : 'currency';
                    $totalAmount = $price->calculateTotalAmount($price->discounted_raw_amount, $companyVatRate->vat_multiplier);

                    $isCompanyBasedVatRate = true;
                    $companyBasedTotalAmount = $totalAmount;
                    $companyBasedVatRateId = $companyVatRate->id;
                    $companyBasedVatRateName = $companyVatRate->name;
                    $companyBasedVatRate = $companyVatRate->rate;
                    $companyBasedVatRateMultiplier = $companyVatRate->vat_multiplier;
                }
            }
        } elseif (Str::upper($currency->iso_4217) != Str::upper($requestCurrencyIso4217)) {
            $currency = Currency::where('iso_4217', $requestCurrencyIso4217)->first();
            $isPriceCurrency = false;
        }

        $converted = false;
        $exchangeRate = null;

        if (! $isPriceCurrency) {
            $converted = true;
            $currency = Currency::where('iso_4217', $requestCurrencyIso4217)->first();

            if ($input->exchangeRateOverride !== null) {
                $exchangeRate = $input->exchangeRateOverride;
                $rawAmount = (int) round($rawAmount * $exchangeRate, 0);
            } else {
                $rawAmount = (int) CurrencyExchange::convertTo(
                    $rawAmount,
                    mb_strtoupper($requestCurrencyIso4217),
                    decimals: 0,
                    round: 'round'
                );
                $exchangeRate = CurrencyExchange::getExchangeRate(mb_strtoupper($requestCurrencyIso4217));
            }

            if ($isCompanyBasedVatRate) {
                $totalAmount = (int) ($rawAmount * (1 + $companyBasedVatRateMultiplier));
            } else {
                $totalAmount = (int) ($rawAmount * (1 + $price->vat_multiplier));
            }

            if ($isCompanyBasedVatRate) {
                $companyBasedTotalAmount = $totalAmount;
            }
        }

        $paidAt = $input->paidAt ?? now()->format('Y-m-d H:i:s');
        $orderId = uniqid('ORD');

        $modularousPayload = [
            'locale' => $input->locale ?? app()->getLocale(),
            'previous_url' => $input->previousUrl,
            'datetime' => $paidAt,
            'original_amount' => $price->raw_amount,
            'original_raw_amount' => $price->discounted_raw_amount,
            'discount_percentage' => $price->discount_percentage,
            'discount_amount' => $price->raw_amount - $price->discounted_raw_amount,
            'subtotal' => $price->discounted_raw_amount,
            'original_total_amount' => $price->total_amount,

            'vat_rate_id' => $price->vat_rate_id,
            'vat_rate_name' => $price->vatRate->name,
            'vat_percentage' => $price->vat_percentage,
            'vat_multiplier' => $price->vat_multiplier,

            'using_country_based_vat_rates' => $useCountryBasedVatRates,
            'vat_rate_from' => $vatRateFrom,
            'company_type' => $companyType,
            'is_company_based_vat_rate' => $isCompanyBasedVatRate,
            'company_based_vat_rate_id' => $companyBasedVatRateId,
            'company_based_vat_rate_name' => $companyBasedVatRateName,
            'company_based_vat_percentage' => $companyBasedVatRate,
            'company_based_vat_multiplier' => $companyBasedVatRateMultiplier,
            'company_based_total_amount' => $companyBasedTotalAmount,

            'converted' => $converted,
            'converted_raw_amount' => $rawAmount,
            'converted_total_amount' => $totalAmount,
            'original_currency_id' => $price->currency_id,
            'original_currency' => $price->currency->iso_4217,
            'converted_currency_id' => $currency->id,
            'converted_currency' => $currency->iso_4217,
            'exchange_rate' => $exchangeRate,
        ];

        $hasTransactionFee = Modularous::shouldIncludeTransactionFee() && $paymentService->has_transaction_fee;
        $transactionFeePercentage = 0.0;
        $transactionFeeAmount = 0.0;
        $totalAmountWithoutTransactionFee = $totalAmount;

        if ($hasTransactionFee) {
            $transactionFeePercentage = $paymentService->transaction_fee_percentage;
            $transactionFeeMultiplier = $transactionFeePercentage / 100;
            $transactionFeeAmount = round($totalAmount * $transactionFeeMultiplier, 0);
            $totalAmount = (int) ($totalAmount + $transactionFeeAmount);
        }

        $modularousPayload['total_amount_without_transaction_fee'] = $totalAmountWithoutTransactionFee;
        $modularousPayload['transaction_fee_exists'] = $hasTransactionFee;
        $modularousPayload['transaction_fee_percentage'] = $transactionFeePercentage;
        $modularousPayload['transaction_fee_amount'] = $transactionFeeAmount;
        $modularousPayload['total_amount_with_transaction_fee'] = $totalAmount;

        $paymentPayload = [
            'amount' => $totalAmount,
            'currency' => $currency->iso_4217,
            'currency_id' => $currency->id,
            'order_id' => $orderId,
            'installment' => 1,
            'status' => PaymentStatus::COMPLETED,
            'payment_gateway' => $paymentService->key,
            'payment_service_id' => $paymentService->id,
            'email' => $user->email,
            'custom_creator_id' => $user->id,
            'custom_creator_type' => $user::class,
            'custom_guard_name' => Modularous::getAuthGuardName(),
            'parameters' => [
                'modularous' => $modularousPayload,
            ],
            'response' => [],
        ];

        if ($price->payment && in_array($price->payment->status, [PaymentStatus::PENDING, PaymentStatus::FAILED], true)) {
            $paymentPayload['id'] = $price->payment->id;
        }

        return new PaymentCalculationResult(
            price: $price,
            user: $user,
            paymentService: $paymentService,
            currency: $currency,
            orderId: $orderId,
            amount: $totalAmount,
            modularousPayload: $modularousPayload,
            paymentPayload: $paymentPayload,
            vatRateFrom: $vatRateFrom,
            isCompanyBasedVatRate: $isCompanyBasedVatRate,
            converted: $converted,
            exchangeRate: $exchangeRate,
            hasTransactionFee: $hasTransactionFee,
            transactionFeePercentage: $transactionFeePercentage,
            transactionFeeAmount: $transactionFeeAmount,
            totalAmountWithoutTransactionFee: $totalAmountWithoutTransactionFee,
            companyType: $companyType,
            paidAt: $paidAt,
        );
    }
}
