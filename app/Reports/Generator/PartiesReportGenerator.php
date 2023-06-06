<?php

namespace App\Reports\Generator;

use App\Http\Resources\EntryResource;
use App\Models\Tenant\Account;
use App\Models\Tenant\AccountType;
use App\Models\Tenant\Entry;
use App\Models\Tenant\EntryTransaction;
use App\ProfitTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Else_;

class PartiesReportGenerator extends BaseReportGenerator
{
    public static function partiesTotalInEveryCurr(Request $request)
    {
        $from_date = request('from_date');
        $to_date = request('to_date');

        if ($from_date == null) $from_date = '0001-01-01';
        if ($to_date == null) $to_date = '9999-12-31';

        $headers = [

            [
                'text' => __('name'),
                'value' => 'party_name'
            ],
            [
                'text' => __('dollar_balance'),
                'value' => 'dolar'
            ],
            [
                'text' => __('shekel_balance'),
                'value' => 'shekel'
            ],
            [
                'text' => __('dinar_balance'),
                'value' => 'dinar'
            ],
            [
                'text' => __('euro_balance'),
                'value' => 'euro'
            ],
            [
                'text' => __('derham_balance'),
                'value' => 'derham'
            ],
            [
                'text' => __('reyal_balance'),
                'value' => 'reyal'
            ],
            [
                'text' => __('pound_balance'),
                'value' => 'pound'
            ],
        ];

        $from_date = Carbon::parse($from_date)->toDateString();
        $to_date = Carbon::parse($to_date)->toDateString();
        $show_zeros = request('show_zeros');

        if ($show_zeros == 1) {
            $sql = "
            select Party_Name, sum(dolar) dolar, sum(shekel) shekel, sum(dinar) dinar, sum(euro) euro, sum(derham) derham, sum(rial) rial, sum(pound) pound, sum(sum_using_tr_ex_rate) sum_using_tr_exchange, sum(sum_using_last_ex_rate) as sum_using_last_ex_rate 
            from (
                select p.name as Party_Name, 
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 1) as dolar,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 2) as shekel,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 3) as dinar,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 4) as euro,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 5) as derham,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 6) as rial,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 7) as pound,
                    sum(e.creditor - e.debtor) / e.exchange_rate as sum_using_tr_ex_rate,
                    Sum(e.creditor - e.debtor) / get_cur_exch_rate(e.currency_id) as sum_using_last_ex_rate
                from parties p inner join entry_transactions e
                            using(account_id)
                            inner join currencies cur on cur.id = e.currency_id
                            where e.created_at::date >= '0001-01-01' and e.created_at::date <= '9999-12-31'  __FOR_One_party 
                            group by (p.name,e.currency_id,e.exchange_rate)
            ) a 
            group by Party_Name;
            ";
        } else {
            $sql = "
            select Party_Name, sum(dolar) dolar, sum(shekel) shekel, sum(dinar) dinar, sum(euro) euro, sum(derham) derham, sum(rial) rial, sum(pound) pound, sum(sum_using_tr_ex_rate) sum_using_tr_exchange, sum(sum_using_last_ex_rate) as sum_using_last_ex_rate 
            from (
                select p.name as Party_Name, 
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 1) as dolar,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 2) as shekel,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 3) as dinar,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 4) as euro,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 5) as derham,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 6) as rial,
                    sum(e.creditor - e.debtor) filter (where e.currency_id = 7) as pound,
                    sum(e.creditor - e.debtor) / e.exchange_rate as sum_using_tr_ex_rate,
                    Sum(e.creditor - e.debtor) / get_cur_exch_rate(e.currency_id) as sum_using_last_ex_rate
                from parties p inner join entry_transactions e
                            using(account_id)
                            inner join currencies cur on cur.id = e.currency_id
                            where e.created_at::date >= '0001-01-01' and e.created_at::date <= '2050-12-21' __FOR_One_party  
                            group by (p.name,e.currency_id,e.exchange_rate) HAVING   SUM(e.creditor - e.debtor) <> 0
            ) a 
            group by Party_Name
            ";
        }

        if (request('party_id')) {
            $sql =  str_replace('__FOR_One_party', "and p.id = " . request('party_id'), $sql);
        } else {
            $sql =  str_replace('__FOR_One_party', " " . request('party_id'), $sql);
        }
        $parties = DB::select($sql);

        if ($request->download == true) {
            $report_headers = [
                __('name'),
                __('dollar_balance'),
                __('shekel_balance'),
                __('dinar_balance'),
                __('euro_balance'),
                __('derham_balance'),
                __('reyal_balance'),
                __('pound_balance'),
            ];
            // $options = ['transaction_type' => [1 => 1], 'document_type' => [1 => __('transfer'), 3 => __('exchange')]];
            return parent::returnFile($parties, $report_headers);
        }
        return response()->json(['items' => $parties, 'headers' => $headers]);
    }
    public static function partiesTotalInOneCurr(Request $request)
    {
        $from_date = request('from_date');
        $to_date = request('to_date');
        $currency_id = request('currency_id');

        if ($from_date == null) $from_date = '0001-01-01';
        if ($to_date == null) $to_date = '9999-12-31';

        $headers = [

            [
                'text' => __('name'),
                'value' => 'p_name'
            ],
            [
                'text' => __('total_creditor'),
                'value' => 'total_creditor'
            ],
            [
                'text' => __('total_debtor'),
                'value' => 'total_debtor'
            ],

            [
                'text' => __('total'),
                'value' => 'total'
            ],
        ];

        $from_date = Carbon::parse($from_date)->toDateString();
        $to_date = Carbon::parse($to_date)->toDateString();

        $sql = "
        select p_name , cur_name  ,total_creditor ,  total_debtor , sum(total_debtor-total_creditor) as total  from (select   p.name as p_name , cur.id as cur_id , cur.name as cur_name , sum(e.creditor )  as total_creditor , sum(e.debtor)  as total_debtor
        from parties p inner join entry_transactions e
        using(account_id)
		inner join currencies cur on cur.id = e.currency_id
        where e.created_at::date >= '$from_date' and e.created_at::date <= '$to_date' 
		group by (p.id , p.name ,  cur.name , cur_id) )w 
		where w.cur_id  =  $currency_id
		group by   cur_name ,p_name , total_creditor , total_debtor
        ";

        $parties = DB::select($sql);

        if ($request->download == true) {
            $report_headers = [
                __('name'),
                __('currency'),
                __('total_creditor'),
                __('total_debtor'),
                __('total'),
            ];
            // $options = ['transaction_type' => [1 => 1], 'document_type' => [1 => __('transfer'), 3 => __('exchange')]];
            return parent::returnFile($parties, $report_headers);
        }
        return response()->json(['items' => $parties, 'headers' => $headers]);
    }
}
