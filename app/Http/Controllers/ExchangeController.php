<?php

namespace App\Http\Controllers;

use App\Events\DocumentDeletedEvent;
use App\Events\DocumentStoredEvent;
use App\Events\DocumentUpdatedEvent;
use App\Http\Requests\StoreExchangeRequest;
use App\Http\Requests\UpdateExchangeRequest;
use App\Http\Resources\ExchangeResource;
use App\Models\Exchange;
use App\Models\ExchangeDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class ExchangeController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Exchange");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Exchange::class, Str::snake("Exchange"));
    }
    public function index(Request $request)
    {
        return ExchangeResource::collection(Exchange::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreExchangeRequest $request)
    {
        $validated = $request->validated();
        $items = $validated['items'];
        unset($validated['items']);

        // if (!hasAbilityToCreateModelInCurrency($validated['currency_id']))
        //     return response()->json(['message' => [__('u dont have an account to complete the proceess')]], 422);
        foreach ($items as $item) {
            if (!hasAbilityToCreateModelInCurrency($item['currency_id']))
                return response()->json(['message' => [__('u dont have an account to complete the proceess')]], 422);
        }
        $exchange = Exchange::create($validated);
        foreach ($items as $item) {
            $exchange->details()->save(
                ExchangeDetail::create($item + ['exchange_id' => $exchange->id])
            );
        }
        DocumentStoredEvent::dispatch($exchange);
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exchange->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        // return $exchange;

        return new ExchangeResource($exchange);
    }
    public function show(Request $request, Exchange $exchange)
    {
        return new ExchangeResource($exchange);
    }
    public function update(UpdateExchangeRequest $request, Exchange $exchange)
    {
        $exchange->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $exchange->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ExchangeResource($exchange);
    }
    public function destroy(Request $request, Exchange $exchange)
    {
        DocumentDeletedEvent::dispatch($exchange);
        $exchange->status = 255;
        $exchange->save();

        return new ExchangeResource($exchange);
    }
}
