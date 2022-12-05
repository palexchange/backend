<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExchangeDetailRequest;
use App\Http\Requests\UpdateExchangeDetailRequest;
use App\Http\Resources\ExchangeDetailResource;
use App\Models\ExchangeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class ExchangeDetailController extends Controller
{

    public static function routeName()
    {
        return Str::snake("ExchangeDetail");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(ExchangeDetail::class, Str::snake("ExchangeDetail"));
    }
    public function index(Request $request)
    {
        return ExchangeDetailResource::collection(ExchangeDetail::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreExchangeDetailRequest $request)
    {
        if (!hasAbilityToCreateModelInCurrency($request->validated()['currency_id']))
            return response()->json(['message' => [__('u dont have an account to complete the proceess')]], 422);

        $exchangeDetail = ExchangeDetail::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exchangeDetail->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ExchangeDetailResource($exchangeDetail);
    }
    public function show(Request $request, ExchangeDetail $exchangeDetail)
    {
        return new ExchangeDetailResource($exchangeDetail);
    }
    public function update(UpdateExchangeDetailRequest $request, ExchangeDetail $exchangeDetail)
    {
        $exchangeDetail->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exchangeDetail->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ExchangeDetailResource($exchangeDetail);
    }
    public function destroy(Request $request, ExchangeDetail $exchangeDetail)
    {
        $exchangeDetail->delete();
        return new ExchangeDetailResource($exchangeDetail);
    }
}
