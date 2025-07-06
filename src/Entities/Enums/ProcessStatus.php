<?php

namespace Unusualify\Modularity\Entities\Enums;

enum ProcessStatus: string
{
    case PREPARING = 'preparing';
    case WAITING_FOR_CONFIRMATION = 'waiting_for_confirmation';
    case WAITING_FOR_REACTION = 'waiting_for_reaction';
    case REJECTED = 'rejected';
    case CONFIRMED = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::PREPARING => __('Preparing'),
            self::WAITING_FOR_CONFIRMATION => __('Waiting for Action'),
            self::WAITING_FOR_REACTION => __('Waiting for Reaction'),
            self::REJECTED => __('Rejected'),
            self::CONFIRMED => __('Confirmed'),
            default => __('Processing'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PREPARING => 'info',
            self::WAITING_FOR_CONFIRMATION => 'warning',
            self::WAITING_FOR_REACTION => 'warning',
            self::REJECTED => 'error',
            self::CONFIRMED => 'success',
            default => 'grey',
        };
    }

    public function cardColor(): string
    {
        return match ($this) {
            self::PREPARING => 'grey',
            self::WAITING_FOR_CONFIRMATION => 'blue-darken-1',
            self::WAITING_FOR_REACTION => 'blue-darken-1',
            self::REJECTED => 'red-darken-1',
            self::CONFIRMED => 'green-darken-1',
            default => 'grey',
        };
    }

    public function cardVariant(): string
    {
        return match ($this) {
            self::PREPARING => 'outlined',
            self::WAITING_FOR_CONFIRMATION => 'outlined',
            self::WAITING_FOR_REACTION => 'outlined',
            self::REJECTED => 'tonal',
            self::CONFIRMED => 'tonal',
            default => 'text',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::PREPARING => 'mdi-progress-clock',
            self::WAITING_FOR_CONFIRMATION => 'mdi-clock-check-outline',
            self::WAITING_FOR_REACTION => 'mdi-clock-check-outline',
            self::REJECTED => 'mdi-close-circle-outline',
            self::CONFIRMED => 'mdi-check-circle-outline',
            default => 'mdi-clock',
        };
    }

    public function nextActionLabel(): string
    {
        return match ($this) {
            self::PREPARING => __('Send for Confirmation'),
            self::WAITING_FOR_CONFIRMATION => __('Confirm'),
            self::WAITING_FOR_REACTION => __('Confirm'),
            self::REJECTED => __('Resend'),
            self::CONFIRMED => __('Confirmed'),
            default => __('Process'),
        };
    }

    public function statusReasonLabel(): string
    {
        return match ($this) {
            self::PREPARING => __('Preparing'),
            self::WAITING_FOR_CONFIRMATION => __('Arrangement'),
            self::WAITING_FOR_REACTION => __('Arrangement'),
            self::REJECTED => __('Reason'),
            self::CONFIRMED => __('Confirmation Reason'),
            default => __('Reason'),
        };
    }

    public function nextActionColor(): string
    {
        return match ($this) {
            self::PREPARING => 'secondary',
            self::WAITING_FOR_CONFIRMATION => 'success',
            self::WAITING_FOR_REACTION => 'success',
            self::REJECTED => 'secondary',
            self::CONFIRMED => 'success',
            default => 'primary',
        };
    }

    public function informationalMessage(): string
    {
        return match ($this) {
            self::PREPARING => __('The contents are being prepared or updated. Please check back later.'),
            // self::WAITING_FOR_CONFIRMATION => __('The contents are being prepared or updated. Please check back later.'),
            // self::WAITING_FOR_REACTION => __('The contents are being prepared or updated. Please check back later.'),
            self::REJECTED => __('The contents has been rejected. The reason is under review, you will be informed soon.'),
            self::CONFIRMED => __('The contents are confirmed.'),
            default => __('The contents are being prepared or updated. Please check back later.'),
        };
    }

    public static function get($caseName): ?string
    {
        foreach (self::cases() as $case) {
            if ($case->name == $caseName) {
                return $case->value;
            }
        }

        return null;
    }
}
