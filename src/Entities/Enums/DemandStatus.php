<?php

namespace Unusualify\Modularity\Entities\Enums;

enum DemandStatus: string
{
    case PENDING = 'pending';
    case EVALUATED = 'evaluated';
    case IN_REVIEW = 'in_review';
    case ANSWERED = 'answered';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::EVALUATED => __('Evaluated'),
            self::IN_REVIEW => __('In Review'),
            self::ANSWERED => __('Answered'),
            self::REJECTED => __('Rejected'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'text-warning',
            self::EVALUATED => 'text-info',
            self::IN_REVIEW => 'text-primary',
            self::ANSWERED => 'text-success',
            self::REJECTED => 'text-error',
        };
    }

    public function iconColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::EVALUATED => 'info',
            self::IN_REVIEW => 'primary',
            self::ANSWERED => 'success',
            self::REJECTED => 'error',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PENDING => 'mdi-clock-outline',
            self::EVALUATED => 'mdi-eye-check-outline',
            self::IN_REVIEW => 'mdi-magnify',
            self::ANSWERED => 'mdi-check-circle-outline',
            self::REJECTED => 'mdi-close-circle-outline',
        };
    }

    public function timeIntervalDescription(): string
    {
        return match ($this) {
            self::PENDING => __('Submitted'),
            self::EVALUATED => __('Evaluated'),
            self::IN_REVIEW => __('Under Review'),
            self::ANSWERED => __('Answered'),
            self::REJECTED => __('Rejected'),
        };
    }

    public function timeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'font-weight-bold text-warning',
            self::EVALUATED => 'font-weight-bold text-info',
            self::IN_REVIEW => 'font-weight-bold text-primary',
            self::ANSWERED => 'font-weight-bold text-success',
            self::REJECTED => 'font-weight-bold text-error',
        };
    }

    public function allowsResponse(): bool
    {
        return match ($this) {
            self::EVALUATED, self::IN_REVIEW, self::ANSWERED => true,
            default => false,
        };
    }

    public function isActive(): bool
    {
        return match ($this) {
            self::PENDING, self::EVALUATED, self::IN_REVIEW => true,
            default => false,
        };
    }
}
