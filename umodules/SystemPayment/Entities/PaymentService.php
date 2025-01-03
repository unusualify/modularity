<?php

namespace Modules\SystemPayment\Entities;

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
        'published',
        'title',
        'is_external',
        'is_internal',
        'button_style',
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

    /**
     * The cardTypes that belong to the PaymentService.
     */
    public function cardTypes(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\Modules\SystemPayment\Entities\CardType::class);
    }
}
