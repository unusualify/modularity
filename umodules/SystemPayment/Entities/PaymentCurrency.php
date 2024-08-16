<?php

namespace Modules\SystemPayment\Entities;



use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class PaymentCurrency extends \Modules\SystemPricing\Entities\Currency
{

	protected $fillable = [
		'name'
	];
	/**
	 * The paymentServices that belong to the Currency.
	 *
	 */
	public function paymentServices() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
	{
		return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentService::class);
	}

}
