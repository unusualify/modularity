<?php

namespace Modules\SystemPayment\Entities;

use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasImages;

class CardType extends Model
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
		'card_type'
	];



	/**
	 * The paymentServices that belong to the CardType.
	 *
	 */
	public function paymentServices() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
	{
		return $this->belongsToMany(\Modules\SystemPayment\Entities\PaymentService::class);
	}

}
