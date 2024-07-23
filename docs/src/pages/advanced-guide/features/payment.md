---


---

# Payment

## HasPayment (Trait)

This trait, defines a relationship between a model and it's price information by leveraging from Unusualify\Priceable package. See below : 

```php
<?php

namespace Modules\Package\Entities;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasPayment;

class PackageCountry extends Model
{
  /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */

	use HasPayment;

	protected $fillable = [
		'name',
		'published',
		'package_region_id'
	];

  /**
	* Get the packageRegion of the packagecountry.
	*
	*/
	public function packageRegion(): \Illuminate\Database\Eloquent\Relations\BelongsTo
	{
		return $this->belongsTo(\Modules\Package\Entities\PackageRegion::class, 'package_region_id', 'id');
	}

    /**
     * Get all of the post's comments.
     */
    public function packages(): MorphMany
    {
        return $this->morphMany(Package::class, 'packageable');
    }
}
```
(See [Unusualify/Priceable](https://github.com/unusualify/priceable){target="_blank"})

With the help of this trait and package, each model record that has HasPayment trait can have multiple price records with different price types, currencies and VAT rates. Related models must have HasPriceable trait.

```php
<?php

namespace Modules\Package\Entities;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Unusualify\Modularity\Entities\Model;
use Unusualify\Modularity\Entities\Traits\HasFiles;
use Unusualify\Priceable\Traits\HasPriceable;

class Package extends Model
{
    use HasPriceable, HasFiles;

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'name',
		'published',
		'description'
	];

    public function packageable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
	 * The packageFeatures that belong to the package.
	 *
	 */
	public function packageFeatures() :BelongsToMany
	{
		return $this->belongsToMany(\Modules\Package\Entities\PackageFeature::class)
            ->using(PackagePackageFeature::class)
            ->withPivot('position', 'active');
	}

    /**
	 * The packageLanguages that belong to the package.
	 *
	 */
	public function packageLanguages() :BelongsToMany
	{
		return $this->belongsToMany(\Modules\Package\Entities\PackageLanguage::class);
	}
}
```

## PaymentTrait (Trait)

This trait, creates a single price for all related model records under the same relation with same currency. To be able to that that we must define the given attribute in the repository that we want to use this functionality.
```php
public $paymentTraitRelationName; //Required
public $paymentTraitDefaultCurrencyId = 2; //Optional
```
Since abstract Repository class already has this trait after defining the $paymentTraitRelationName trait will do it's functionality.

For example :

Let's assume we have two different models our first model called Package that has HasPriceable trait. HasPriceable trait will add price, currency etc. features to the model. Second model called PackageCountry, this model has morphMany relationship with Packages and HasPayment trait that we talked about earlier.

Since Package model has HasPriceable trait it will automatically have a price, currency etc. and our PackageCountry model has relation with the Package model. With the help of the PaymentTrait we will be able to create a single price record for PackageCountry model's record as well. 

In short, this trait lets you manage prices for inherited models on inheretance basis, offering flexibility in your pricing strategy.

(See [Unusualify/Payable](https://github.com/unusualify/payable){target="_blank"})