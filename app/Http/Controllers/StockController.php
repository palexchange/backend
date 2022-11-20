<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Stock");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Stock::class, Str::snake("Stock"));
    }
    public function index(Request $request)
    {
        // $this->pagination = Currency::count();
        return StockResource::collection(Stock::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreStockRequest $request)
    {
        $stocks = $request->validated();
        foreach ($stocks as $stock) {
            $stock = Stock::updateOrCreate(['ref_currency_id' => $stock['ref_currency_id'], 'currency_id' => $stock['currency_id']], $stock);
            $stock_trans = ['stock_id' => $stock->id, 'selling_price' => $stock->final_selling_price, 'purchasing_price' => $stock->final_purchasing_price];
            if ($stock->closed_at) {
                $stock_trans += ['time' => $stock->closed_at];
                $stock_trans += ['closing' => true];
            }
            StockTransaction::create($stock_trans);
        }
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $stock->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new StockResource($stock);
    }
    public function show(Request $request, Stock $stock)
    {
        return new StockResource($stock);
    }
    public function update(UpdateStockRequest $request, Stock $stock)
    {
        $stock->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $stock->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new StockResource($stock);
    }
    public function destroy(Request $request, Stock $stock)
    {
        $stock->delete();
        return new StockResource($stock);
    }
}
