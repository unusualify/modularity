<?php

namespace Unusualify\Modularity\Facades;

use Illuminate\Support\Facades\Facade;

class Redirect extends Facade
{
	protected static function getFacadeAccessor(): string
	{
		return 'modularity.redirect';
	}
}
