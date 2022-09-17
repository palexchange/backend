<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreFiscalYearRequest;
use App\Http\Requests\UpdateFiscalYearRequest;
use App\Http\Resources\FiscalYearResource;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class FiscalYearController extends Controller
{

    public static function routeName(){
        return Str::snake("FiscalYear");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(FiscalYear::class,Str::snake("FiscalYear"));
    }
    public function index(Request $request)
    {
        return FiscalYearResource::collection(FiscalYear::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreFiscalYearRequest $request)
    {
        $fiscalYear = FiscalYear::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $fiscalYear->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new FiscalYearResource($fiscalYear);
    }
    public function show(Request $request,FiscalYear $fiscalYear)
    {
        return new FiscalYearResource($fiscalYear);
    }
    public function update(UpdateFiscalYearRequest $request, FiscalYear $fiscalYear)
    {
        $fiscalYear->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $fiscalYear->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new FiscalYearResource($fiscalYear);
    }
    public function destroy(Request $request,FiscalYear $fiscalYear)
    {
        $fiscalYear->delete();
        return new FiscalYearResource($fiscalYear);
    }
}
