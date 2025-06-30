<?php

namespace Unusualify\Modularity\Entities;

use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use Modules\SystemUser\Entities\Company;
use Spatie\Permission\Traits\HasRoles;
use Unusualify\Modularity\Database\Factories\UserFactory;
use Unusualify\Modularity\Entities\Traits\Auth\CanRegister;
use Unusualify\Modularity\Entities\Traits\HasFileponds;
use Unusualify\Modularity\Entities\Traits\HasOauth;
use Unusualify\Modularity\Entities\Traits\HasScopes;
use Unusualify\Modularity\Entities\Traits\IsTranslatable;
use Unusualify\Modularity\Entities\Traits\ModelHelpers;
use Unusualify\Modularity\Notifications\GeneratePasswordNotification;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens,
        HasFactory,
        HasRoles,
        HasScopes,
        IsTranslatable,
        ModelHelpers,
        Notifiable,
        HasFileponds,
        HasOauth,
        CanRegister;

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
        'country_id',
        'password',
        'published',
        'email_verified_at',
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

    protected $appends = [
        'company_name',
        'name_with_company',
    ];

    protected $isCreatingCompany = false;

    protected $bootingCompanyName = null;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->password == null) {
                $model->password = Hash::make(env('DEFAULT_USER_PASSWORD', 'Hj84TlN!'));
            }

            if ($model->company_name && $model->company_id == null) {
                $model->isCreatingCompany = true;
                $model->bootingCompanyName = $model->company_name;
            }

            $model->offsetUnset('company_name');
        });

        static::created(function ($model) {
            if ($model->isCreatingCompany) {
                $model->company_id = Company::create([
                    'name' => $model->bootingCompanyName,
                ])->id;
            }
        });

        static::updated(function ($model) {
            if ($model->isDirty('email')) {
                $model->email_verified_at = null;
                $model->saveQuietly();
            }
        });
    }

    public function initialize()
    {
        parent::initialize();

        dd(
            class_uses_recursive($this)
        );

        $this->mergeFillable([
            'company_name',
        ]);

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

    protected function validCompany(): Attribute
    {
        $valid = true;

        if ($this->company_id != null) {
            $valid = true;
            foreach ($this->company->getAttributes() as $attr => $value) {
                if (! str_contains($attr, '_at') && $attr != 'id') {
                    if (! $value) {
                        $valid = false;

                        break;
                    }
                }
            }
        }

        return Attribute::make(
            get: fn () => $valid,
        );
    }

    protected function companyName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->company()->exists() ? $this->company->name : null,
        );
    }

    protected function nameWithCompany(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name . ' (' . ($this->company_name ? $this->company_name : __('System User')) . ')',
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

    protected function avatar(): Attribute
    {
        return new Attribute(
            get: fn ($value) => $this->fileponds()
                ->where('role', 'avatar')
                ->first()?->mediableFormat()['source'] ?? '/vendor/modularity/jpg/anonymous.jpg',
        );
    }

    /**
     * Send the password generate notification.
     *
     * @param string $token
     * @return void
     */
    public function sendGeneratePasswordNotification($token)
    {
        $this->notify(new GeneratePasswordNotification($token));
    }

    /**
     * Get the email address that should be used for the password generate notification.
     *
     * @return string
     */
    public function getEmailForPasswordGeneration()
    {
        return $this->email;
    }

    public function getTable()
    {
        return modularityConfig('tables.users', parent::getTable());
    }

    protected static function newFactory(): \Illuminate\Database\Eloquent\Factories\Factory
    {
        return UserFactory::new();
    }

    public function sendEmailVerification($token)
    {
        $this->notify(new \Unusualify\Modularity\Notifications\EmailVerification($token));
    }
}
