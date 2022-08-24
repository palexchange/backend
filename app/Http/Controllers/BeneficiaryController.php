<?php

namespace App\Http\Controllers;
use App\Models\Beneficiary;
use App\Http\Controllers\Controller;
use App\Http\Resources\BeneficiaryResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBeneficiaryRequest;
use App\Http\Requests\UpdateBeneficiaryRequest;

use Illuminate\Support\Facades\Validator;

class BeneficiaryController extends Controller
{

    public static function routeName(){
        return Str::snake("Beneficiary");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(Beneficiary::class,Str::snake("Beneficiary"));
    }
    public function index(Request $request)
    {
        return BeneficiaryResource::collection(Beneficiary::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreBeneficiaryRequest $request)
    {
        $beneficiary = Beneficiary::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $beneficiary->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new BeneficiaryResource($beneficiary);
    }
    public function show(Request $request,Beneficiary $beneficiary)
    {
        return new BeneficiaryResource($beneficiary);
    }
    public function update(UpdateBeneficiaryRequest $request, Beneficiary $beneficiary)
    {
        $beneficiary->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $beneficiary->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new BeneficiaryResource($beneficiary);
    }
    public function destroy(Request $request,Beneficiary $beneficiary)
    {
        $beneficiary->delete();
        return new BeneficiaryResource($beneficiary);
    }
}
