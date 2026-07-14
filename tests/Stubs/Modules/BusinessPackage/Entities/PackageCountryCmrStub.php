<?php

namespace Modules\BusinessPackage\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Test double for {@see PackageCountry} in modularous package tests.
 */
class PackageCountryCmrStub extends Model
{
    protected $table = 'package_countries';

    public $timestamps = false;
}
