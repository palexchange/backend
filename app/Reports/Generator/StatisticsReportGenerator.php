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

    public static function countTransfers()
    {

        $now = Carbon::now()->toDateString();
        $transfers = DB::select("
        WITH months AS (SELECT * FROM generate_series(1, 12) AS t(n))
            SELECT TO_CHAR( TO_DATE(months.n::TEXT , 'MM'),'Month') AS MONTH , COUNT(issued_at) as transfers_count  FROM transfers
            RIGHT JOIN months ON EXTRACT(MONTH from issued_at) = months.n 
            where EXTRACT(YEAR from NOW())  = EXTRACT(YEAR from issued_at)
            GROUP BY months.n  
            ORDER By months.n;
            ");
        $exchanges = DB::select("
        WITH months AS (SELECT * FROM generate_series(1, 12) AS t(n))
            SELECT TO_CHAR( TO_DATE(months.n::TEXT , 'MM'),'Month') AS MONTH , COUNT(issued_at) as transfers_count  FROM transfers
            RIGHT JOIN months ON EXTRACT(MONTH from issued_at) = months.n 
            where EXTRACT(YEAR from NOW())  = EXTRACT(YEAR from issued_at)
            GROUP BY months.n  
            ORDER By months.n;
            ");
        $months = [];
        $counts = [];
        foreach ($transfers as $el) {
            $months[] = $el->month;
            $counts[] = $el->transfers_count;
        }

        $data = compact('months', 'counts');
        return response()->json(compact('data'), 200);
    }

    public static function exchangeRransfersCount()
    {

        $counts = DB::select(
            "
        SELECT 'transfers' AS table_name, COUNT(*) FROM transfers where created_at::DATE = CURRENT_DATE
        UNION
        SELECT 'exchanges' AS table_name, COUNT(*) FROM exchanges where created_at::date = CURRENT_DATE"
        );
        return response()->json(['items' => $counts, 'headers' => []]);
        // return response()->json(compact('counts'), 200);
    }
}
