<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Unusualify\Modularity\Database\Factories\UserFactory;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasScopes;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasScopes, IsTranslatable, ModelHelpers, Notifiable, HasFileponds;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'surname',
        'job_title',
        'email',
        'language',
        'timezone',
        'phone',
        'country',
        'password',
        'published',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->password == null) {
                $model->password = Hash::make(env('DEFAULT_USER_PASSWORD', 'Hj84TlN!'));
            }
            // dd($model);
        });
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function setImpersonating($id)
    {
        Session::put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        Session::forget('impersonate');
    }

    public function isImpersonating()
    {
        return Session::has('impersonate');
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('superadmin');

        return $this->roles === 'SUPERADMIN';
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    protected function invalidCompany(): Attribute
    {
        $inValid = false;

        if ($this->company_id != null) {
            foreach ($this->company->getAttributes() as $attr => $value) {
                if (! str_contains($attr, '_at') && $attr != 'id') {
                    if ($value == null) {
                        $inValid = true;
                    }
                }
            }
        }

        return Attribute::make(
            get: fn () => $inValid,
        );
    }

    public function scopeCompanyUser($query)
    {
        return $query->whereNotNull("{$this->getTable()}.company_id");
    }

    public function isClient()
    {
        return preg_match('/client-/', $this->roles[0]->name);
    }

    public function getTable()
    {
        return modularityConfig('tables.users', parent::getTable());
    }

    protected static function newFactory()
    {
        return new UserFactory;
    }
}
