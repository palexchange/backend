<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreEntryTransactionRequest;
use App\Http\Requests\UpdateEntryTransactionRequest;
use App\Http\Resources\EntryTransactionResource;
use App\Models\EntryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class EntryTransactionController extends Controller
{

    public static function routeName(){
        return Str::snake("EntryTransaction");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(EntryTransaction::class,Str::snake("EntryTransaction"));
    }
    public function index(Request $request)
    {
        return EntryTransactionResource::collection(EntryTransaction::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreEntryTransactionRequest $request)
    {
        $entryTransaction = EntryTransaction::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $entryTransaction->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new EntryTransactionResource($entryTransaction);
    }
    public function show(Request $request,EntryTransaction $entryTransaction)
    {
        return new EntryTransactionResource($entryTransaction);
    }
    public function update(UpdateEntryTransactionRequest $request, EntryTransaction $entryTransaction)
    {
        $entryTransaction->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $entryTransaction->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new EntryTransactionResource($entryTransaction);
    }
    public function destroy(Request $request,EntryTransaction $entryTransaction)
    {
        $entryTransaction->delete();
        return new EntryTransactionResource($entryTransaction);
    }
}
