<?php

namespace Unusualify\Modularity\Entities\Enums;

enum AssignmentStatus: string
{
    case COMPLETED = 'completed';
    case PENDING = 'pending';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::COMPLETED => __('Completed'),
            self::PENDING => __('Pending'),
            self::REJECTED => __('Rejected'),
            self::CANCELLED => __('Cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMPLETED => 'text-success',
            self::PENDING => 'text-warning',
            self::REJECTED => 'text-error',
            self::CANCELLED => 'text-grey',
        };
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::COMPLETED => 'success',
            self::PENDING => 'info',
            self::REJECTED => 'error',
            self::CANCELLED => 'grey',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::COMPLETED => 'mdi-check-circle-outline',
            self::PENDING => 'mdi-clock-outline',
            self::REJECTED => 'mdi-close-circle-outline',
            self::CANCELLED => 'mdi-close-circle-outline',
        };
    }

    public function timeIntervalDescription(): string
    {
        return match ($this) {
            self::PENDING => __('Until'),
            self::REJECTED => __('Rejected'),
            self::CANCELLED => __('Cancelled'),
            self::COMPLETED => __('Completed'),
        };
    }

    public function timeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'font-weight-bold text-blue-darken-1',
            self::REJECTED => 'font-weight-bold text-error',
            self::CANCELLED => 'font-weight-bold text-warning',
            self::COMPLETED => 'font-weight-bold text-success',
        };
    }
}
