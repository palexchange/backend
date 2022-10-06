<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    // protected $with = ['accounts'];
    protected $appends = ['active_accounts'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

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
    // protected $casts = [
    //     'email_verified_at' => 'datetime',
    // ];
    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
    }
    public function accounts()
    {
        return $this->hasManyThrough(
            Account::class, //1
            UserAccount::class, //2
            'user_id', //0
            'id', //2
            'id', // 0
            'account_id' // 2
        );
    }
    public function getActiveAccountsAttribute()
    {
        return $this->accounts()->where('status', 1)->get()->toArray();
    }
}
