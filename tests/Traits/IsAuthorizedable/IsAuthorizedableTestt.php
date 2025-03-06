<?php

namespace Unusualify\Modularity\Tests\Traits\IsAuthorizedable;


use Illuminate\Database\Eloquent\Model;
use Unusualify\Modularity\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Unusualify\Modularity\Entities\Authorized;
use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\Traits\IsAuthorizedable;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Facades\Modularity;

class IsAuthorizedableTest extends TestCase
{
    use RefreshDatabase;

    protected $testModel;
    protected $user;
    protected $admin;
    protected $company;
}

// Test model that uses the IsAuthorizedable trait
class TestAuthorizedModel extends Model
{
    use IsAuthorizedable;

    protected $table = 'test_authorized_models';
    protected $fillable = ['name'];

    // Define if soft delete is used
    public function isSoftDeletable()
    {
        return true;
    }
}
