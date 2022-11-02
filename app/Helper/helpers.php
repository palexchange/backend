<?php

use App\Models\Setting;
use App\Models\FiscalYear;
use Carbon\Carbon;

function ilog($data, $clear = false)
{
    $file = storage_path() . '/logs/debug.log';
    $fileHandle = null;
    $fileHandle = fopen($file, $clear ? 'w' : 'a');
    if (is_array($data)) {
        $data = json_encode($data);
    }
    $date = Carbon::now();
    $data = "[$date] app.logger: $data";
    fwrite($fileHandle, $data . PHP_EOL);
    fclose($fileHandle);
}
function year_end()
{
    $financial_year = FiscalYear::latest()->first();
    if ($financial_year) {
        return $financial_year;
    } else {
        $end_year_setting = Setting::find("financial_year_end")->value;
        $new_financial_year = new FiscalYear();
        $new_financial_year->tenant_id = auth()->user()->tenant_id;
        $new_financial_year->start_at = Carbon::parse($end_year_setting)->year(Carbon::now()->toDateString())->addDay();
        $new_financial_year->end_at = Carbon::parse($end_year_setting)->year(Carbon::now()->year);
        $new_financial_year->save();
        return $new_financial_year;
    }
}
function hasAbilityToCreateModelInCurrency($currencies_id)
{
    $accounts_count = auth()->user()->accounts()->where('status', 1)->whereIn('accounts.currency_id', $currencies_id)->count();
    return $accounts_count > 0;
}
