---




---


# Payment


## HasPayment (Trait)


This trait, defines a relationship between a model and it's price information by leveraging from the Unusualify\Priceable package. See below :


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
}
```
(See [Unusualify/Priceable](https://github.com/unusualify/priceable){target="_blank"})


With the help of this trait and package, each model record that has HasPayment trait can have multiple price records with different price types, currencies and VAT rates. Related models must have HasPriceable trait.


## PaymentTrait (Trait)


This trait creates a single price for all related model records under the same relation with the same currency. To be able to do that we must define the given attribute in the repository that we want to use this functionality.


```php
<?php


namespace Modules\Package\Repositories;


use Unusualify\Modularity\Repositories\Repository;
use Modules\Package\Entities\PackageCountry;
use Unusualify\Modularity\Repositories\Traits\PaymentTrait;


class PackageCountryRepository extends Repository
{
  
   public $paymentTraitRelationName = 'packages';


   public function __construct(PackageCountry $model)
   {
       $this->model = $model;
   }
}


```
Since abstract Repository class already has this trait after defining the $paymentTraitRelationName trait will do it's functionality. The related model to the PackageCountry must have HasPriceable trait.


For example :


Let's assume we have two different models, our first model called Package that has a HasPriceable trait. HasPriceable trait will add price, currency etc. features to the model. Second model is called PackageCountry, this model has a relationship with the Packages model and has a HasPayment trait that we talked about earlier.


Since the Package model has a HasPriceable trait it will automatically have a price, currency etc. and our PackageCountry model has relation with the Package model. With the help of the PaymentTrait we will be able to create a single price record for the PackageCountry model's record as well.


In short, this trait lets you manage prices for inherited models on inheritance basis, offering flexibility in your pricing strategy.


(See [Unusualify/Payable](https://github.com/unusualify/payable){target="_blank"})



