<?php

namespace Modules\SystemUser\Repositories;

use Unusualify\Modularity\Entities\Company;
use Unusualify\Modularity\Entities\User;
use Unusualify\Modularity\Repositories\Repository;
use Unusualify\Modularity\Repositories\Traits\FilepondsTrait;
use Unusualify\Modularity\Repositories\Traits\OauthTrait;

class UserRepository extends Repository
{
    use FilepondsTrait, OauthTrait;

    public $exceptRelations = [
        // 'roles'
    ];

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * @param \Laravel\Socialite\Contracts\User $oauthUser
     * @return \A17\Twill\Models\User
     */
    public function oauthCreateUser($oauthUser)
    {
        $fullName = $oauthUser->name;
        $nameWithSurname = name_surname_resolver($fullName);
        $nameArray = array_slice($nameWithSurname, 0, count($nameWithSurname) - 1);
        $name = implode(' ', $nameArray);
        $surname = end($nameWithSurname);
        $email = $oauthUser->email;

        $company = Company::create([
            'name' => "$name's Company",
        ]);

        $user = $this->model->firstOrNew([
            'name' => $name,
            'surname' => $surname,
            'email' => $email,
            'published' => true,
            'company_id' => $company->id,
        ] /* + $roleKeyValue */);

        $user->save();

        $user->touch('email_verified_at');

        $user->assignRole('client-manager');

        return $user;
    }
}
