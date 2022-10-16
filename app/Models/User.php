<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
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

    protected $appends = ['active_accounts', 'daily_exchange_transactions', 'daily_exchange_profit'];
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
        $query->when($request->currency_id &&  $request->type_id, function ($q) use ($request) {
            $q
                ->join('user_accounts', 'user_accounts.user_id', 'users.id')
                ->join('accounts', 'user_accounts.account_id', 'accounts.id')
                ->leftJoin('currencies', 'currencies.id', 'accounts.currency_id')
                ->leftJoin('entry_transactions', 'entry_transactions.account_id', 'accounts.id')
                ->where('user_accounts.status', 1)
                ->where('accounts.currency_id', $request->currency_id)
                ->where('accounts.type_id', $request->type_id)->select('users.name AS user_name', 'accounts.name AS account_name', 'accounts.id AS on_account_id', 'currencies.name AS currency_name', DB::raw('case when (entry_transactions.debtor >= 0 ) then sum(entry_transactions.debtor -  entry_transactions.creditor) else  0 end  as balance '))
                ->groupBy('user_name', 'account_name', 'accounts.id', 'currencies.name', 'entry_transactions.debtor');
        });
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
    public function getDailyExchangeTransactionsAttribute()
    {

        $count = Exchange::whereDate('created_at', Carbon::today())->count();
        return  $count;
    }
    public function getDailyExchangeProfitAttribute()
    {
        $sum = EntryTransaction::where('account_id', 3)->whereDate('created_at', Carbon::today())->sum('creditor');
        return  $sum;
    }
}
