<?php

namespace Modules\SystemPayment\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Unusualify\Modularous\Entities\Model;
use Unusualify\Modularous\Entities\Traits\HasImages;
use Unusualify\Modularous\Entities\Traits\HasSpreadable;
use Unusualify\Payable\Payable;

class PaymentService extends Model
{
    use HasImages, HasSpreadable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'key',
        'published',
        'transaction_fee_percentage',
        'is_external',
        'is_internal',
        'button_style',
    ];

    protected $appends = [
        'has_built_in_form',
        'button_logo_url',
        'transferrable',
        'bank_details',
        'has_transaction_fee',
    ];

    protected $casts = [
        'transaction_fee_percentage' => 'float',
    ];

    protected $with = [
        // 'paymentCurrencies',
    ];

    protected function hasTransactionFee(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->transaction_fee_percentage > 0.00,
        );
    }

    /**
     * Get the payments for the PaymentService.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * The currencies that belong to the PaymentService.
     */
    public function paymentCurrencies(): BelongsToMany
    {
        return $this->belongsToMany(PaymentCurrency::class);
    }

    public function internalPaymentCurrencies(): HasMany
    {
        return $this->hasMany(PaymentCurrency::class, 'payment_service_id', 'id');
    }

    /**
     * The cardTypes that belong to the PaymentService.
     */
    public function cardTypes(): BelongsToMany
    {
        return $this->belongsToMany(CardType::class);
    }

    protected function serviceClass(): Attribute
    {

        return Attribute::make(
            get: function () {
                $serviceClass = null;
                $paymentGateway = null;

                try {
                    $paymentGateway = $this->key;
                } catch (\Throwable $th) {
                    // throw $th;
                }

                if ($paymentGateway) {

                    try {
                        $serviceClass = Payable::getServiceClass($paymentGateway);
                    } catch (\Exception $e) {

                        try {
                            // code...
                            // Check transferrable status directly from spreadable relationship instead of using the accessor
                            $isTransferrable = $this->spreadable && isset($this->spreadable->content['type']) && $this->spreadable->content['type'] == 2;

                            if ($e->getMessage() == 'Service class not found for slug: ' . $paymentGateway && $isTransferrable) {
                                $serviceClass = new class extends \Unusualify\Payable\Services\PaymentService
                                {
                                    public function __construct()
                                    {
                                        $this->mode = 'test';
                                        $this->config = [];
                                    }

                                    public function hydrateParams(array|object $params): array
                                    {
                                        return $params;
                                    }
                                };
                            } else {
                                throw $e;
                            }
                        } catch (\Throwable $th) {
                            dd($paymentGateway, $serviceClass, $this, $th);
                        }
                    }
                }

                return $serviceClass;
            },
        );
    }

    protected function hasBuiltInForm(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->serviceClass ? $this->serviceClass::$hasBuiltInForm : false,
        );
    }

    protected function buttonLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->image('button_logo', locale: app()->getLocale(), has_fallback: true) ?? $this->image('logo', locale: app()->getLocale()),
        );
    }

    protected function transferrable(): Attribute
    {
        $type = 1;

        try {
            $type = $this->spreadable->content['type'] ?? 1;
        } catch (\Throwable $th) {
            // throw $th;
        }

        return Attribute::make(
            get: fn ($value) => $type == 2,
        );
    }

    protected function bankDetails(): Attribute
    {
        $keys = ['account_holder', 'iban', 'swift_code', 'description', 'address'];

        return Attribute::make(
            get: fn ($value) => collect($keys)->reduce(function ($acc, $key) {
                $transferDetails = $this->spreadable->content['transfer_details'] ?? [];
                if ($transferDetails && isset($transferDetails[$key])) {
                    $acc[$key] = $transferDetails[$key];
                }

                return $acc;
            }, []),
        );
    }

    public function scopeIsExternal($query)
    {
        return $query->where("{$this->getTable()}.is_external", 1);
    }

    public function scopeIsTransfer($query)
    {
        return $query->whereHas('spreadable', function ($query) {
            $query->where('content->type', 2);
        });
    }

    public function scopeIsInternal($query)
    {
        return $query->where("{$this->getTable()}.is_internal", 1);
    }
}
