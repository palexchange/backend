<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Maatwebsite\Excel\Concerns\ToArray;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    protected $casts = [
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d',
    ];
    // protected $with = ['accounts'];

    protected $appends = [
        'start_main_active_accounts',
        'main_active_accounts', 'active_accounts',
        'daily_exchange_transactions',
        'daily_exchange_profit',
        'daily_transfer_profit',
        'exchnages_profit',
        'transfers_profit',
        'funds_accounts_balance',
        'start_funds_accounts_balance'
    ];
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
                        ->where('users.id', '!=', auth()->user()->id)
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
    public function entries()
    {
        return $this->hasMany(Entry::class);
    }
    public function getActiveAccountsAttribute()
    {

        // return  auth()->user()->role == 1 ? Account::where('type_id', 3)->get()->toArray() : $this->accounts()->where('status', 1)->get()->toArray();
        return  $this->accounts()->where('status', 1)
            ->orderBy('accounts.id')
            ->get()
            ->map(function ($account) {
                $profit_accounts_id =
                    Setting::whereIn(
                        'key',
                        [
                            'exchange_difference_account_id',
                            'returned_commission_account_id',
                            'office_commission_account_id',
                            'transfers_commission_account_id'
                        ]
                    )->pluck('value')->all();

                return collect($account->toArray())
                    ->put(
                        'price',
                        EntryTransaction::join('entries', 'entries.id', 'entry_id')->whereIn('account_id', $profit_accounts_id)
                            ->where('on_account_balance_id', $account->id)
                            ->where('type', 1)
                            ->sum(DB::raw(' creditor - debtor '))
                    )->all();
            })->toArray();
    }
    public function getMainActiveAccountsAttribute()
    {

        return  $this->accounts()->where('status', 1)
            ->where('main', true)
            ->orderBy('accounts.id')
            ->get()
            ->map(function ($account) {
                $profit_accounts_id =
                    Setting::whereIn(
                        'key',
                        [
                            'exchange_difference_account_id',
                            'returned_commission_account_id',
                            'office_commission_account_id',
                            'transfers_commission_account_id'
                        ]
                    )->pluck('value')->all();
                return collect($account->toArray())
                    ->put(
                        'price',
                        EntryTransaction::join('entries', 'entries.id', 'entry_id')->whereIn('account_id', $profit_accounts_id)
                            ->where('on_account_balance_id', $account->id)
                            ->where('type', 1)
                            ->sum(DB::raw(' creditor - debtor '))
                    )->all();
            })->toArray();
    }
    public function getStartMainActiveAccountsAttribute()
    {

        return  $this->accounts()->where('status', 1)
            ->where('main', true)
            ->orderBy('accounts.id')
            ->get()
            ->map(function ($account) {
                $profit_accounts_id =
                    Setting::whereIn(
                        'key',
                        [
                            'exchange_difference_account_id',
                            'returned_commission_account_id',
                            'office_commission_account_id',
                            'transfers_commission_account_id'
                        ]
                    )->pluck('value')->all();
                return collect($account->toArray())
                    ->put(
                        'start_price',
                        EntryTransaction::join('entries', 'entries.id', 'entry_id')->whereIn('account_id', $profit_accounts_id)
                            ->where('on_account_balance_id', $account->id)
                            ->where('type', 1)
                            ->whereDate('entry_transactions.created_at', '<', Carbon::today()->toDateString())
                            ->sum(DB::raw(' creditor - debtor '))

                    )->all();
            })->toArray();
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
        $count = 0;
        if ($this->role != 1) {
            $count = Exchange::where('user_id', $this->id)
                ->where('status', 1)
                ->whereDate('created_at', Carbon::today())->count();
        } else {
            $count = Exchange::where('status', 1)
                ->whereDate('created_at', Carbon::today())->count();
        }
        return  $count;
    }
    public $open_total_dollars = 0;
    public $total_start_dollars = 0;
    public $total_final_dollars = 0;
    public function getDailyExchangeProfitAttribute()
    {


        // $this->accounts()->where('status', 1)->get()
        //     ->each(function ($account) {
        //         $this->total_dollars = $this->total_dollars + $account->net_balance_in_dollar;
        //     });
        $ss = $this->accounts()
            ->leftJoin('entry_transactions', 'accounts.id', 'entry_transactions.account_id')
            ->leftJoin('entries', 'entries.id', 'entry_transactions.entry_id')
            ->where(DB::raw('entries.date::date'), '=', Carbon::today()->toDateString())
            ->where('user_accounts.status', 1)->distinct()
            ->get()
            ->each(function ($account) {
                $mid = $account->mid;
                $close_mid = $account->close_mid;
                $start = 0;
                $end = 0;
                if ($account->currency_id == 4) {
                    $start =  ($account->net_balance * $mid) - ($account->net_balance_today * $mid);
                    $end =  ($account->net_balance * $close_mid);
                } else {
                    $start =  ($account->net_balance / $mid) - ($account->net_balance_today / $mid);
                    $end =  ($account->net_balance / $close_mid);
                }
                $this->total_start_dollars += $start;
                $this->total_final_dollars += $end;
            });

        return   $this->total_final_dollars - $this->total_start_dollars;
    }
    public function getDailyTransferProfitAttribute()
    {
        $sum = 0;
        $profit_accounts_id =
            Setting::whereIn(
                'key',
                [
                    'exchange_difference_account_id',
                    'returned_commission_account_id',
                    'office_commission_account_id',
                    'transfers_commission_account_id'
                ]
            )->pluck('value')->all();
        $sum = 0;
        if ($this->role == 1) {
            $sum = Entry::join('entry_transactions', 'entry_transactions.entry_id', 'entries.id')
                ->whereIn('account_id', $profit_accounts_id)
                ->whereDate('entry_transactions.created_at', Carbon::today()->toDateString())
                ->sum(DB::raw('ac_creditor - ac_debtor '));
        } else {
            $sum = Entry::join('entry_transactions', 'entry_transactions.entry_id', 'entries.id')
                ->where('entries.user_id', $this->id)
                ->whereIn('account_id', $profit_accounts_id)
                ->whereDate('entry_transactions.created_at', Carbon::today()->toDateString())
                ->sum(DB::raw('ac_creditor - ac_debtor '));
        }

        return  $sum;
    }


    public function getExchnagesProfitAttribute()
    {
        $sum = $this->entries()
            ->join('entry_transactions', 'entry_transactions.entry_id', 'entries.id')
            ->where('account_id', 3)
            ->sum(DB::raw('creditor -debtor '));

        return $sum;
    }
    public function getTransfersProfitAttribute()
    {

        $sum = $this->entries()
            ->join('entry_transactions', 'entry_transactions.entry_id', 'entries.id')
            ->where('account_id', 2)
            ->sum(DB::raw('creditor -debtor'));
        return $sum;
    }

    // public function getInputsAccountsAttribute()
    // {

    //     $accounts = $this->entries()
    //         ->join('entry_transactions', 'entry_transactions.entry_id', 'entries.id')
    //         ->join('accounts', 'entry_transactions.account_id', 'entry_transactions.account_id')
    //         ->whereIn('entry_transactions.account_id', [35, 36, 37, 38, 39, 40, 41])->get();
    //     return $accounts;
    // }

    public function getFundsAccountsBalanceAttribute()
    {


        $sql = '
        select  round(sum(sub_table.balance)::numeric  , 3)  as balance ,
        sub_table.acc_currency_id as currency_id ,sub_table.name 
       from  (
           select 
           case when ac.type_id in (5) then sum(et.creditor- et.debtor) else sum(et.debtor- et.creditor) end  as balance 
           , et.currency_id , crr.name  as name ,et.currency_id as acc_currency_id  
           from
               entry_transactions et
                 join accounts ac on ac.id = et.account_id 
                 join entries en on en.id = et.entry_id 
                 join currencies crr on crr.id = et.currency_id 
        where ac.is_transaction = true and 
        en.type = 1 and 
        ac.type_id in (4 ,3 , 5 )  
		and en.document_sub_type not in (4,5) 
		and et.transaction_type not in (6, 8, 10, 2, 3, 4, 9)
       group by et.currency_id ,crr.name ,ac.name,ac.currency_id , ac.type_id )
	   as sub_table group by sub_table.name , sub_table.acc_currency_id order by currency_id asc';
        $data = DB::select($sql);
        return $data;
    }
    public function getStartFundsAccountsBalanceAttribute()
    {
        $sql = ' select  round(sum(sub_table.balance)::numeric  , 3)  as balance ,
        sub_table.acc_currency_id as currency_id ,sub_table.name 
       from  (
           select 
           case when ac.type_id in (5) then sum(et.creditor- et.debtor) else sum(et.debtor- et.creditor) end  as balance 
           , et.currency_id , crr.name  as name ,et.currency_id as acc_currency_id  
           from
               entry_transactions et
                 join accounts ac on ac.id = et.account_id 
                 join entries en on en.id = et.entry_id 
                 join currencies crr on crr.id = et.currency_id 
        where ac.is_transaction = true and 
        en.type = 1 and 
        ac.type_id in (4 ,3 , 5 )  
		and en.document_sub_type not in (4,5 ) 
		and et.transaction_type not in (6, 8, 10, 2, 3, 4, 9) and  en.date < CURRENT_DATE 
       group by et.currency_id ,crr.name ,ac.name,ac.currency_id , ac.type_id )
	   as sub_table group by sub_table.name , sub_table.acc_currency_id order by currency_id asc';
        $data = DB::select($sql);
        return $data;
    }
}
