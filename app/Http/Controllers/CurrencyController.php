<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreCurrencyRequest;
use App\Http\Requests\UpdateCurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class CurrencyController extends Controller
{

    public static function routeName(){
        return Str::snake("Currency");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(Currency::class,Str::snake("Currency"));
    }
    public function index(Request $request)
    {
        return CurrencyResource::collection(Currency::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreCurrencyRequest $request)
    {
        $currency = Currency::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $currency->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CurrencyResource($currency);
    }
    public function show(Request $request,Currency $currency)
    {
        return new CurrencyResource($currency);
    }
    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $currency->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CurrencyResource($currency);
    }
    public function destroy(Request $request,Currency $currency)
    {
        $currency->delete();
        return new CurrencyResource($currency);
    }
}
