<?php

declare(strict_types=1);

namespace Unusualify\Modularous\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\SystemPayment\Entities\Payment;
use Modules\SystemPayment\Entities\PaymentService;
use Modules\SystemPricing\Entities\Price;
use Unusualify\Modularous\Entities\CreatorRecord;
use Unusualify\Modularous\Entities\Enums\PaymentStatus;
use Unusualify\Modularous\Entities\User;
use Unusualify\Modularous\Facades\Modularous;

final class PaymentCalculationResult
{
    /**
     * @param array<string, mixed> $modularousPayload
     * @param array<string, mixed> $paymentPayload
     */
    public function __construct(
        public readonly Price $price,
        public readonly User $user,
        public readonly PaymentService $paymentService,
        public readonly Model $currency,
        public readonly string $orderId,
        public readonly int $amount,
        public readonly array $modularousPayload,
        public readonly array $paymentPayload,
        public readonly string $vatRateFrom,
        public readonly bool $isCompanyBasedVatRate,
        public readonly bool $converted,
        public readonly ?float $exchangeRate,
        public readonly bool $hasTransactionFee,
        public readonly float $transactionFeePercentage,
        public readonly float $transactionFeeAmount,
        public readonly int $totalAmountWithoutTransactionFee,
        public readonly string $companyType,
        public readonly string $paidAt,
    ) {}

    /**
     * @return array<string, string|int|float|bool|null>
     */
    public function summaryRows(): array
    {
        return [
            'User ID' => $this->user->id,
            'User Email' => $this->user->email,
            'Company Type' => $this->companyType,
            'Price ID' => $this->price->id,
            'Order ID' => $this->orderId,
            'VAT Rate From' => $this->vatRateFrom,
            'Company Based VAT' => $this->isCompanyBasedVatRate ? 'Yes' : 'No',
            'Converted' => $this->converted ? 'Yes' : 'No',
            'Exchange Rate' => $this->exchangeRate,
            'Original Currency' => $this->modularousPayload['original_currency'] ?? null,
            'Converted Currency' => $this->modularousPayload['converted_currency'] ?? null,
            'Original Total' => $this->modularousPayload['original_total_amount'] ?? null,
            'Converted Raw' => $this->modularousPayload['converted_raw_amount'] ?? null,
            'Converted Total' => $this->modularousPayload['converted_total_amount'] ?? null,
            'Transaction Fee' => $this->hasTransactionFee ? 'Yes' : 'No',
            'Transaction Fee Amount' => $this->transactionFeeAmount,
            'Final Amount' => $this->amount,
            'Paid At' => $this->paidAt,
        ];
    }

    public function toInsertSql(?int $paymentId = null): string
    {
        $table = config('payable.table', 'up_payments');
        $parameters = json_encode(
            ['modularous' => $this->modularousPayload],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $response = json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $status = PaymentStatus::COMPLETED->value;
        $columns = [
            'payment_service_id',
            'price_id',
            'currency_id',
            'payment_gateway',
            'order_id',
            'amount',
            'currency',
            'status',
            'email',
            'installment',
            'parameters',
            'response',
            'created_at',
            'updated_at',
        ];

        $values = [
            (string) $this->paymentService->id,
            (string) $this->price->id,
            (string) $this->currency->id,
            self::sqlString($this->paymentService->key),
            self::sqlString($this->orderId),
            (string) $this->amount,
            self::sqlString($this->currency->iso_4217),
            self::sqlString($status),
            self::sqlString($this->user->email),
            '1',
            self::sqlString($parameters),
            self::sqlString($response),
            self::sqlString($this->paidAt),
            self::sqlString($this->paidAt),
        ];

        if ($paymentId !== null) {
            array_unshift($columns, 'id');
            array_unshift($values, (string) $paymentId);
        }

        return sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s);',
            $table,
            implode('`, `', $columns),
            implode(', ', $values)
        );
    }

    public function toCreatorRecordInsertSql(int $paymentId): string
    {
        $table = (new CreatorRecord)->getTable();
        $paymentClass = Payment::class;
        $creatorType = $this->user::class;
        $guardName = Modularous::getAuthGuardName();

        return sprintf(
            'INSERT INTO `%s` (`creatable_type`, `creatable_id`, `creator_type`, `creator_id`, `guard_name`) VALUES (%s, %d, %s, %d, %s);',
            $table,
            self::sqlString($paymentClass),
            $paymentId,
            self::sqlString($creatorType),
            $this->user->id,
            self::sqlString($guardName)
        );
    }

    private static function sqlString(?string $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }
}
