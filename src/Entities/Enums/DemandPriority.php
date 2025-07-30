<?php

namespace Unusualify\Modularity\Entities\Enums;

enum DemandPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::LOW => __('Low'),
            self::MEDIUM => __('Medium'),
            self::HIGH => __('High'),
            self::URGENT => __('Urgent'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LOW => 'text-grey',
            self::MEDIUM => 'text-info',
            self::HIGH => 'text-warning',
            self::URGENT => 'text-error',
        };
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::LOW => 'grey',
            self::MEDIUM => 'info',
            self::HIGH => 'warning',
            self::URGENT => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LOW => 'mdi-arrow-down',
            self::MEDIUM => 'mdi-minus',
            self::HIGH => 'mdi-arrow-up',
            self::URGENT => 'mdi-alert',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
        };
    }
}
