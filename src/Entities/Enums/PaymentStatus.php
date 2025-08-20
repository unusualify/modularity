<?php

namespace Unusualify\Modularity\Entities\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case FAILED = 'FAILED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
    case REFUNDED = 'REFUNDED';

    public static function get($caseName)
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName) {
                return $case->value;
            }
        }

        return null;
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::FAILED => __('Failed'),
            self::COMPLETED => __('Completed'),
            self::CANCELLED => __('Cancelled'),
            self::REFUNDED => __('Refunded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'grey',
            self::FAILED => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'error',
            self::REFUNDED => 'grey',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'mdi-clock-alert-outline',
            self::FAILED => 'mdi-close-circle-outline',
            self::COMPLETED => 'mdi-check-circle-outline',
            self::CANCELLED => 'mdi-close-circle-outline',
            self::REFUNDED => 'mdi-credit-card-refund-outline',
        };
    }
}
