<?php

namespace Unusualify\Modularity\Entities\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'Unpaid';
    case PARTIALLY_PAID = 'Partially Paid';
    case PAID = 'Paid';
    case CANCELLED = 'Cancelled';
    case REFUNDED = 'Refunded';

    public static function get($caseName)
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName) {
                return $case->value;
            }
        }

        return null;
    }
}
