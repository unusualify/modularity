<?php

namespace Modules\SystemPayment\Entities;

use Unusualify\Modularity\Entities\Model;



class PaymentService extends Model 
{
    
    /**
	 * The attributes that are mass assignable.
	 * 
	 * @var array<int, string>
	 */ 
	protected $fillable = [
		'name',
		'published',
		'title'
	];

    
    

	/**
	 * Get the payments for the PaymentService.
	 * 
	 */
	public function payments() : \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(\Modules\SystemPayment\Entities\Payment::class);
	}

	/**
	 * The currencies that belong to the PaymentService.
	 * 
	 */
	public function currencies() : \Illuminate\Database\Eloquent\Relations\BelongsToMany
	{
		return $this->belongsToMany(\Modules\SystemPayment\Entities\Currency::class);
	}

}
