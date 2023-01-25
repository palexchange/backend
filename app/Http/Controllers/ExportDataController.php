<?php

namespace App\Http\Controllers;

use App\Exports\ExportTable;
use App\Exports\TransferExport;
use App\Models\ExportData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ExportDataResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreExportDataRequest;
use App\Http\Requests\UpdateExportDataRequest;
use App\Models\Transfer;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ExportDataController extends Controller
{

    public static function routeName()
    {
        return Str::snake("ExportData");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(ExportData::class, Str::snake("ExportData"));
    }
    public function index(Request $request)
    {
        $model = ucfirst($request->model);
        $get_export_model = "App\\Exports\\" . $model . 'Export';
        return Excel::download(new $get_export_model($request), $model . ".xlsx", null, ['moew_moew_header' => 'moew moew']);
        // return ExportDataResource::collection(ExportData::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreExportDataRequest $request)
    {
        $exportData = ExportData::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exportData->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ExportDataResource($exportData);
    }
    public function show(Request $request, ExportData $exportData)
    {
        return new ExportDataResource($exportData);
    }
    public function update(UpdateExportDataRequest $request, ExportData $exportData)
    {
        $exportData->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exportData->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ExportDataResource($exportData);
    }
    public function destroy(Request $request, ExportData $exportData)
    {
        $exportData->delete();
        return new ExportDataResource($exportData);
    }
}
