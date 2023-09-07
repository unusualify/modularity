<?php

namespace OoBook\CRM\Base\Entities;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use Spatie\Permission\Traits\HasRoles;
use OoBook\CRM\Base\Entities\Traits\HasHelpers;
use OoBook\CRM\Base\Entities\Traits\IsTranslatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasHelpers, IsTranslatable;

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
            if($model->password == null){
                $model->password = Hash::make(env('DEFAULT_USER_PASSWORD', 'Hj84TlN!'));
            }
            // dd($model);
        });
    }

    public function company()
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


    protected function invalidCompany():Attribute
    {
        $inValid = false;

        if($this->company_id != null){
            foreach ($this->company->getAttributes() as $attr => $value) {
                if (!str_contains($attr, '_at') && $attr != 'id') {
                    if ($value == null)
                        $inValid = true;
                }
            }
        }

        return Attribute::make(
            get: fn () => $inValid,
        );
    }

}
