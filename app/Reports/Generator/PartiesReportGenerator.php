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

class PartiesReportGenerator extends BaseReportGenerator
{
    public static function partiesReport(Request $request)
    {
        $from_date = request('from_date');
        $to_date = request('to_date');
        
        if($from_date == null) $from_date = '0001-01-01';
        if($to_date == null) $to_date = '9999-12-31';

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
                'text' => __('debtor'),
                'value' => 'debtor'
            ],
            [
                'text' => __('creditor'),
                'value' => 'creditor'
            ],
            [
                'text' => __('balance'),
                'value' => 'balance'
            ]
        ];

        $from_date = Carbon::parse($from_date)->toDateString();
        $to_date = Carbon::parse($to_date)->toDateString();

        $sql = "select p.id as party_id, p.name as party_name, sum(e.debtor) as debtor,  sum(e.creditor) as creditor, sum(e.debtor - e.creditor) as balance 
        from entry_transactions e 
        INNER join parties p using(account_id)
        where e.created_at::date >= '$from_date' and e.created_at::date <= '$to_date'
        group by(p.name, p.id)";

        $parties = DB::select($sql);

        if ($request->download == true) {
            $report_headers = [__('id'), __('name'), __('debtor'), __('creditor'), __('balance'),];
            $options = ['transaction_type' => [1 => 1], 'document_type' => [1 => __('transfer'), 3 => __('exchange')]];
            return parent::returnFile($parties, $report_headers, $options);
        }
        return response()->json(['items' => $parties, 'headers' => $headers]);
    }
}
