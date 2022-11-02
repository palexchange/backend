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

    protected $appends = ['main_active_accounts', 'active_accounts', 'daily_exchange_transactions', 'daily_exchange_profit'];
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
            $q->select('agg_tabel.balance',  'agg_tabel.account_name', 'agg_tabel.on_account_id', 'user_name', 'currency_name', 'actual_balance')
                ->fromSub(function ($qq) use ($request) {
                    $qq->from('users')->join('user_accounts', 'user_accounts.user_id', 'users.id')
                        ->join('accounts', 'user_accounts.account_id', 'accounts.id')
                        ->leftJoin('currencies', 'currencies.id', 'accounts.currency_id')
                        ->leftJoin('entry_transactions', 'entry_transactions.account_id', 'accounts.id')
                        ->where('user_accounts.status', 1)
                        ->where('accounts.currency_id', $request->currency_id)
                        ->where('accounts.type_id', $request->type_id)
                        ->select('users.name AS user_name', 'accounts.name AS account_name', 'accounts.id AS on_account_id', 'currencies.name AS currency_name', 'accounts.actual_balance', DB::raw('sum(entry_transactions.debtor -  entry_transactions.creditor)  as balance'))
                        ->groupBy('users.id', 'accounts.id', 'currencies.id', 'on_account_id');
                }, 'agg_tabel');
        });

        //     select     agg_tabel.balance ,  agg_tabel.account_name , agg_tabel.on_account_id , user_name
        // from (
        // 	select "users"."name" as "user_name", "accounts"."name" as "account_name", "accounts"."id" 
        // 	as "on_account_id", "currencies"."name" as "currency_name",
        // 	sum(entry_transactions.debtor -  entry_transactions.creditor) as balance from "users" 
        // 	inner join "user_accounts" on "user_accounts"."user_id" = "users"."id" 
        // 		inner join "accounts" on "user_accounts"."account_id" = "accounts"."id" 
        // 			left join "currencies" on "currencies"."id" = "accounts"."currency_id" 
        // 				left join "entry_transactions" on "entry_transactions"."account_id" = "accounts"."id"
        // where "user_accounts"."status" = 1 and "accounts"."currency_id" = 2 and "accounts"."type_id" = 3  
        // 	group by users.id ,accounts.id , currencies.id , on_account_id) as agg_tabel

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
        // return  auth()->user()->role == 1 ? Account::where('type_id', 3)->get()->toArray() : $this->accounts()->where('status', 1)->get()->toArray();
        return  $this->accounts()->where('status', 1)
            ->get()->toArray();
    }
    public function getMainActiveAccountsAttribute()
    {

        return  $this->accounts()->where('status', 1)->where('main', true)
            ->get()->toArray();
    }
    // public function getUserCurrenceisAttribute()
    // {
    //     if (auth()->user()->role == 1) {
    //         return [1, 2, 3, 4, 5, 6];
    //     }
    //     $arr = array_map(function ($v) {
    //         return $v['currency_id'];
    //     }, $this->active_accounts);
    //     return $arr;
    // }
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
