<?php

namespace App\Reports\Generator;


use App\Exports\ExcelExport;
use Maatwebsite\Excel\Facades\Excel;

class BaseReportGenerator
{

    public static function returnFile($items, $headers, $options = null)
    {
        return Excel::download(new ExcelExport($items, $headers, $options), request('type') . "_" . request('sub_type') . ".xlsx", null, ['test_header_test' => 'moew']);
    }
}
