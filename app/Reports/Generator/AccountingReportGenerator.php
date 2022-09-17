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
use Illuminate\Support\Facades\DB;

class AccountingReportGenerator
{
    public static function accountStatementReport(Request $request)
    {
        $prev_balance = null;
        $after_balance = null;
        $account = request('account');

        $headers = [
            [
                'text' => 'journal_no',
                'value' => 'r_id'
            ],
            [
                'text' => __('public.date'),
                'value' => 'date'
            ],
            [
                'text' => __('public.beneficiary_name'),
                'value' => 'beneficiary_name'
            ],
            [
                'text' => __('public.transaction_type'),
                'value' => 'statement'
            ],
            [
                'text' => __('public.transaction_no'),
                'value' => 'document_number'
            ],
            [
                'text' => __('public.reference'),
                'value' => 'reference',
            ],
            [
                'text' => __('public.debtor'),
                'value' => 'debtor'
            ],
            [
                'text' => __('public.creditor'),
                'value' => 'creditor'
            ],
            [
                'text' => __('public.accumulated_balance'),
                'value' => 'a_balance'
            ]
        ];
        if ($account == null)
            return response()->json(['items' => [], 'headers' => $headers]);

        $from = request('from');
        $to = request('to');
        $to = Carbon::now()->addDay()->toDateString();
        $year_end = year_end();
        $from = Carbon::parse(Carbon::parse($year_end->end_at)->subYear()->toDateString())->toDateString();
        if ($request->from)
            $from = $request->from;
        if ($request->to)
            $to = $request->to;
        $last_before = Carbon::parse($from)->subDay()->toDateString();
        $sql = "select *,case when t.document_id is null then null else r_id end as r_id ,case when t.document_id is null then '$last_before' else date end as date from account_statement($account,'$from','$to',false)t order by case when r_id is null then '$last_before'::date else t.date end,t.r_id";

        $entry_accounts = DB::select($sql);
        return response()->json(['items' => $entry_accounts, 'headers' => $headers]);
    }

    public static function getLevels(Request $request)
    {
        $account = null;
        if ($request->account)
            $account = $request->account;
        $sql = ' WITH RECURSIVE cte AS (
            SELECT id, parent_id, 1 AS level
            FROM   accounts
            WHERE  parent_id IS NULL
            ' . ($account != null ? 'AND id = ' . $account : '') . '
            UNION  ALL
            SELECT t.id, t.parent_id, c.level + 1
            FROM   cte      c
            JOIN   accounts t ON t.parent_id = c.id
            )
         SELECT MAX(level) AS max_level
         FROM   cte
         ';
        $level = DB::select($sql);
        return response()->json(compact('level'));
    }
    public static function getChildren(Request $request)
    {
        $id = request('id');
        $to = Carbon::now()->toDateString();
        $from = Carbon::parse(auth()->user()->tenant->created_at);
        if ($request->account)
            $account = $request->account;
        if ($request->level)
            $level = $request->level;
        if ($request->from)
            $from = $request->from;
        if ($request->to)
            $to = $request->to;
        $children = DB::select("select distinct accounts.id,is_leaf(accounts.id) as is_leaf,name,sum(current_balance.debtor) as debtor,sum(current_balance.creditor) as creditor,coalesce(sum(prev.debtor),0) as prev_debtor,coalesce(sum(prev.creditor),0) as prev_creditor from accounts,get_balance(accounts.id,'" . $from . "','" . $to . "') as current_balance left join get_balance(accounts.id,'" . $from . "',NULL) as prev on prev.id=current_balance.id where parent_id =" . $id . " group by accounts.id");

        // $children = DB::select("select distinct accounts.id,is_leaf(accounts.id) as is_leaf,name,sum(debtor) as debtor,sum(creditor) as creditor from accounts,get_balance(id,'" . $from . "','" . $to . "') where parent_id =" . $request->id . " group by accounts.id");
        return response()->json(compact('children'));
    }
}
