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

class AccountingReportGenerator extends BaseReportGenerator
{
    public static function  accountStatementReport(Request $request)
    {
        $prev_balance = null;
        $after_balance = null;
        $show_sender = boolval(request('show_sender'));
        $no_details = boolval(request('no_details'));

        $show_receiver = boolval(request('show_receiver'));
        $account = request('account');
        // App::setlocale('ar');
        $headers = [
            [
                'text' => __('journal_no'), //رقم القيد
                'value' => 'r_id'
            ],
            [
                'text' => __('public.date'), // تاريخ
                'value' => 'date'
            ],
            [
                'text' => __('document_type'), // اسم الحركة
                'value' => 'document_type'
            ],
            // [
            //     'text' => __('document_id'), // اسم الحركة
            //     'value' => 'document_id'
            // ],

        ];
        if ($show_sender) {

            $headers[] =
                [
                    'text' => __('sender_name'), //دائن
                    'value' => 'sender_name'
                ];
        }
        if ($show_receiver) {
            $headers[] =
                [
                    'text' => __('receiver_name'), //دائن
                    'value' => 'receiver_name'
                ];
        }
        if (!$no_details) {

            $headers = array_merge($headers,  [
                [
                    'text' => __('public.debtor'), // مدين
                    'value' => 'debtor'
                ],
                [
                    'text' => __('public.creditor'), //دائن
                    'value' => 'creditor'
                ],
                [
                    'text' => __('public.transaction_type'), // نوع الحوالة
                    'value' => 'transaction_type'
                ],
                [
                    'text' => __('user_name'),
                    'value' => 'user_name'
                ],
                [
                    'text' => __('currency_id'),
                    'value' => 'currency_id'
                ],
                [
                    'text' => __('exchange rate'),
                    'value' => 'exchange_rate'
                ],
            ]);
        }
        $headers = array_merge($headers,  [

            [
                'text' => __('debtor_in_group_curr'), //مدين بعملة المجموهة
                'value' => 'ac_debtor'
            ],
            [
                'text' => __('creditor_in_group_curr'), //دائن بعملة المجموهة
                'value' => 'ac_creditor'
            ],
            [
                'text' => __('public.accumulated_balance'), //دائن بعملة المجموهة
                'value' => 'acc_balance'
            ],
            [
                'text' => __('statement'), // اسم الحركة
                'value' => 'statement'
            ],
        ]);
        // $array = array(1, 2, 3, 4, 5, 6);

        // // positive $offset: an offset from the begining of array  
        // dd(array_slice($array, 2)); // [3,4,5,6]


        //    $headers);
        if ($account == null)
            return response()->json(['items' => [], 'headers' => $headers]);
        // $year_end = year_end();
        // $from = Carbon::parse(Carbon::parse($year_end->end_at)->subYear()->toDateString())->toDateString();
        // if ($request->from)
        //     $from = $request->from;
        // if ($request->to)
        //     $to = $request->to;
        $from = request('from') ?? '2023-01-01 01:00:00';
        $tto = request('to') ?? Carbon::now()->timezone('Asia/Gaza')->toDateTimeString();
        $tto = Carbon::parse($tto)->addDay()->toDateString();
        $from = Carbon::parse($from)->subDay()->toDateString();
        // $user_id = request('user_id');
        // if (auth()->user()['role'] == 1) {
        $user_id =  request('for_user_id') ?? 0;
        // }
        // $user_id = 0;
        // dd($user_id);
        // // $user_id = 0;
        $currency_ids = "NULL";
        $curr_len =  0;
        $currencies = request('currency_id') > 0 ? request('currency_id') : 0;
        if (isset($currencies[0])) {
            $curr_len =  count($currencies);
            $ids = join(',', $currencies);
            $currency_ids = "ARRAY [$ids]";
        }
        // dd($currency_ids);
        $last_before = Carbon::parse($from)->subDay()->toDateString();
        $vars = "";
        if ($show_sender) {

            $vars =  "sender_name,";
        }
        if ($show_receiver) {
            $vars = $vars . "receiver_name,";
        }
        // $test = Carbon::parse($tto)->addDay()->toDateString();
        $sql = "";
        if ($no_details) {
            $sql = "select
            t.document_id   as r_id,
            case when t.document_id is null and user_name is null then '$tto' else date end as date,
            document_type,
            $vars
            ac_debtor,
            ac_creditor,
            acc_balance,
            statement
            from account_statement($account,'$from','$tto',false , $currency_ids ,$curr_len , 'الرصيد الإجمالي', '$user_id')t order by date ";
        } else {
            $sql = "select
            t.document_id   as r_id,
            case when t.document_id is null and user_name is null then '$tto' else date end as date,
            transaction_type,
            debtor,
            creditor,
            document_type,
            user_name,
            $vars
            currency_name,
            currency_id,
            exchange_rate,
            ac_debtor,
            ac_creditor,
            acc_balance,
            statement
            from account_statement($account,'$from','$tto',false , $currency_ids ,$curr_len , 'الرصيد الإجمالي', '$user_id')t order by date ";
        }

        // from account_statement($account,'$from','$tto',false)t order by t.r_id ";
        // $entry_accounts = DB::select($sql);

        // $sql = "select *,case when t.document_id is null then null else r_id end as r_id ,case when t.document_id is null then '$from' else date end as date from account_statement($account,'$from','$tto',false)t order by case when r_id is null then '$last_before'::date else t.date end,t.r_id";
        $entry_accounts = DB::select($sql);

        // account_statement(a_id BIGINT, date_from DATE , date_to DATE,_active_only BOOLEAN)

        // dd($entry_accounts);
        // dd($entry_accounts);
        $report_headers = [
            __('journal_no'), //رقم القيد
            __('public.date'), // تاريخ
            __('public.transaction_type'),

        ];

        if (!$no_details) {
            $report_headers = array_merge($report_headers,  [
                // نوع الحوالة
                __('public.debtor'), // مدين
                __('public.creditor'), //دائن
                __('document_type'), // اسم الحركة
                __('user_name'),
            ]);
        }
        if ($show_sender) {
            $report_headers[] =  __('sender_name');
        }
        if ($show_receiver) {
            $report_headers[] =  __('receiver_name');
        }
        if (!$no_details) {
            $report_headers = array_merge($report_headers, [
                __('currency_id'),
                __('exchange rate'),
                __('currency id')
            ]);
        }
        $report_headers = array_merge($report_headers, [
            __('debtor_in_group_curr'), //مدين بعملة المجموهة
            __('creditor_in_group_curr'), //دائن بعملة المجموهة
            __('public.accumulated_balance'), //دائن بعملة المجموهة
            __('statement'), // اسم الحركة
        ]);

        if ($request->download == true) {
            $options = [
                'transaction_type' => [
                    1 => 1,
                    0 => 0,
                    2 => "عمولة حوالة",
                    3 => "عمولة وسيط",
                    4 => "مرجع",
                    5 => "عمولة موني جرام إضافية",
                    6 => "مبلغ حوالة صادرة",
                    7 => "مصروف",
                    // 8 => "ربحية حوالة",
                    8 => "ربحية من الوسيط",
                    9 => "ربحية فرق عملة",
                    10 => "مبلغ تسليم واردة",
                    // 7 => 7,
                ],
                'document_type' => [
                    1 => __('transfer'),
                    2 => __('exchange'),
                    3 => __('receipt'),
                ]
            ];
            return parent::returnFile($entry_accounts, $report_headers, $options);
        }
        return response()->json(['items' => $entry_accounts, 'headers' => $headers]);
    }
    public  static function currencyCredit(Request $request)
    {
        // App::setlocale('ar');
        // $from = $request->from ?? Carbon::now('UTC')->subDay()->toDateString();
        // $to = $request->to ?? Carbon::now('UTC')->addDay()->toDateString();
        $ids = $request->accounts_ids;


        $ids_text = join(',', $ids);
        $sql = "
        select accounts.name,accounts.id,sum(debtor-creditor) as balance
        from accounts inner join entry_transactions on entry_transactions.account_id = accounts.id
        inner join entries on entries.id=entry_transactions.entry_id
        where entries.status=1
        
        and accounts.id in ($ids_text)
        group by accounts.id
        ";
        // and entries.date between '$from' and '$to'

        $headers = [
            [
                'text' => __('currency_name'),
                'value' => 'name'
            ],
            [
                'text' => __('balance'),
                'value' => 'balance'
            ],

        ];
        $items = DB::select($sql);
        return response()->json(['items' => $items, 'headers' => $headers]);
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
