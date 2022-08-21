<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreStockTransactionRequest;
use App\Http\Requests\UpdateStockTransactionRequest;
use App\Http\Resources\StockTransactionResource;
use App\Models\StockTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class StockTransactionController extends Controller
{

    public static function routeName(){
        return Str::snake("StockTransaction");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(StockTransaction::class,Str::snake("StockTransaction"));
    }
    public function index(Request $request)
    {
        return StockTransactionResource::collection(StockTransaction::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreStockTransactionRequest $request)
    {
        $stockTransaction = StockTransaction::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $stockTransaction->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new StockTransactionResource($stockTransaction);
    }
    public function show(Request $request,StockTransaction $stockTransaction)
    {
        return new StockTransactionResource($stockTransaction);
    }
    public function update(UpdateStockTransactionRequest $request, StockTransaction $stockTransaction)
    {
        $stockTransaction->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $stockTransaction->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new StockTransactionResource($stockTransaction);
    }
    public function destroy(Request $request,StockTransaction $stockTransaction)
    {
        $stockTransaction->delete();
        return new StockTransactionResource($stockTransaction);
    }
}
