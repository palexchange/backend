<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Http\Resources\StockResource;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{

    public static function routeName(){
        return Str::snake("Stock");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(Stock::class,Str::snake("Stock"));
    }
    public function index(Request $request)
    {
        return StockResource::collection(Stock::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreStockRequest $request)
    {
        $stock = Stock::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $stock->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new StockResource($stock);
    }
    public function show(Request $request,Stock $stock)
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
    public function destroy(Request $request,Stock $stock)
    {
        $stock->delete();
        return new StockResource($stock);
    }
}
