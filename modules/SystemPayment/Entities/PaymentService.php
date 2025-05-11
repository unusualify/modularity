<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasImages;

class PaymentService extends Model
{
    use HasImages;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'published',
        'is_external',
        'is_internal',
        'button_style',
    ];

    protected $appends = [
        'button_logo_url',
    ];

    /**
     * Get the payments for the PaymentService.
     */
    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\SystemPayment\Entities\Payment::class);
    }

    /**
     * The currencies that belong to the PaymentService.
     */
    public function paymentCurrencies(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentCurrency::class);
    }

    public function internalPaymentCurrencies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\SystemPayment\Entities\PaymentCurrency::class, 'payment_service_id', 'id');
    }

    /**
     * The cardTypes that belong to the PaymentService.
     */
    public function cardTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\CardType::class);
    }

    protected function buttonLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->image('button_logo', locale: app()->getLocale(), has_fallback: true) ?? $this->image('logo', locale: app()->getLocale()),
        );
    }
}
