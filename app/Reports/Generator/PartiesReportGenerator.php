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
                'text' => __('id'),
                'value' => 'party_id'
            ],
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
            Select *
            from crosstab (
            'select   p.name ,  cur.id , sum(e.creditor - e.debtor) as total
            from parties p inner join entry_transactions e
            using(account_id)
            inner join currencies cur on cur.id = e.currency_id
            where e.created_at::date >= ''$from_date'' and e.created_at::date <= ''$to_date'' 
            group by (p.id , p.name ,  cur.id) '
        ) as ct (party_name varchar, dolar float8 , shekel float8  ,dinar float8  ,euro float8  , derham float8  ,reyal float8 ,pound float8  );
            ";
        } else {
            $sql = "
            Select *
            from crosstab (
            'select   p.name ,  cur.id , sum(e.creditor - e.debtor) as total
            from parties p inner join entry_transactions e
            using(account_id)
            inner join currencies cur on cur.id = e.currency_id
            where e.created_at::date >= ''$from_date'' and e.created_at::date <= ''$to_date'' 
            group by (p.id , p.name ,  cur.id) HAVING   SUM(e.creditor - e.debtor) <> 0'
        ) as ct (party_name varchar, dolar float8 , shekel float8  ,dinar float8  ,euro float8  , derham float8  ,reyal float8 ,pound float8  );
            ";
        }

        $parties = DB::select($sql);

        if ($request->download == true) {
            $report_headers = [__('id'), __('name'), __('debtor'), __('creditor'), __('balance'),];
            $options = ['transaction_type' => [1 => 1], 'document_type' => [1 => __('transfer'), 3 => __('exchange')]];
            return parent::returnFile($parties, $report_headers, $options);
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
                'text' => __('id'),
                'value' => 'party_id'
            ],
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

        // if ($request->download == true) {
        //     $report_headers = [__('id'), __('name'), __('debtor'), __('creditor'), __('balance'),];
        //     $options = ['transaction_type' => [1 => 1], 'document_type' => [1 => __('transfer'), 3 => __('exchange')]];
        //     return parent::returnFile($parties, $report_headers, $options);
        // }
        return response()->json(['items' => $parties, 'headers' => $headers]);
    }
}
