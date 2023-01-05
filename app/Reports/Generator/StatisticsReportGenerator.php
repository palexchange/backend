<?php

namespace App\Reports\Generator;

use App\Models\Exchange;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsReportGenerator
{
    public static function test()
    {
        $from = request('from');
        $to = request('to');
        $account = request('account');
        $to = Carbon::now('UTC')->addDay()->toDateString();
        $last_before = Carbon::parse($from)->subDay()->toDateString();
        $sql = "select *,case when t.document_id is null then null else r_id end as r_id ,case when t.document_id is null then '$last_before' else date end as date from account_statement($account,'$from','$to',false)t order by case when r_id is null then '$last_before'::date else t.date end,t.r_id";
        // dd($sql);
        $entry_accounts = DB::select($sql);
    }

    public static function mothly()
    {

        $now = Carbon::now()->toDateString();
        // $trasfers =
        //     [
        //         'data' => DB::select("select extract(day from dates) as day,coalesce(sum(b.total),0) as total from generate_series( date_trunc('year', '$now','UTC')::date, '$now' , interval '1 day') dates left join (select * from transfers  on b.issued_at=dates group by dates.dates order by extract(day from dates) asc"),
        //         'info' => Transfer::join('bill_items', 'bill_items.bill_id', 'bills.id')->whereRaw('cast(bills.issue_date as date)=current_date::date')->where('bills.bill_type_id', '=', $bill_type_id)->select([DB::raw('coalesce(sum(bills.total),0) as total'), DB::raw('count(item_id) as items'), DB::raw('count(bills.id) as bills_total')])->get()
        //     ];
        // $exchanges =
        //     [
        //         'data' => DB::select("select extract(day from dates) as day,coalesce(sum(b.total),0) as total from generate_series( date_trunc('year', '$now','UTC')::date, '$now' , interval '1 day') dates left join (select * from exchanges where bills.bill_type_id=$bill_type_id and bills.tenant_id =$tenant_id) b on b.issue_date=dates group by dates.dates order by extract(day from dates) asc"),
        //         'info' => Exchange::join('bill_items', 'bill_items.bill_id', 'bills.id')->whereRaw("cast(bills.issue_date as date)>=date_trunc('month',current_date)::date")->where('bills.bill_type_id', '=', $bill_type_id)->select([DB::raw('coalesce(sum(bills.total),0) as total'), DB::raw('count(item_id) as items'), DB::raw('count(bills.id) as bills'), DB::raw('count(bills.id) as bills_total')])->get()
        //     ];

        $data = compact('trasfers', 'exchanges');
        return response()->json(compact('data'), 200);
    }
}
