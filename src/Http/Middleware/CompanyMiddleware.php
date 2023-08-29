<?php

namespace OoBook\CRM\Base\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use OoBook\CRM\Base\Entities\Company;

class CompanyMiddleware{

  /**
   * @var AuthFactory
   */
  protected $authFactory;


  public function __construct(AuthFactory $authFactory)
  {
    $this->authFactory = $authFactory;
  }
  public function handle($request, Closure $next)
  {
    // dd(auth()->user()->invalidCompany);
    if (!($request->routeIs('profile.*') || $request->routeIs('profile'))) {
      if (auth()->user()->invalidCompany) {
        return redirect()->route('profile');
      }
    } 
    return $next($request);
  }
}
