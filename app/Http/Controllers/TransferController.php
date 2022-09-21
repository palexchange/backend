<?php

namespace App\Http\Controllers;

use App\Events\DocumentStoredEvent;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use App\Http\Resources\TransferResource;
use App\Models\Transfer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class TransferController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Transfer");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Transfer::class, Str::snake("Transfer"));
    }
    public function index(Request $request)
    {
        return TransferResource::collection(Transfer::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreTransferRequest $request)
    {
        $transfer = Transfer::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $transfer->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        DocumentStoredEvent::dispatch($transfer);
        return new TransferResource($transfer);
    }
    public function show(Request $request, Transfer $transfer)
    {
        return new TransferResource($transfer);
    }
    public function update(UpdateTransferRequest $request, Transfer $transfer)
    {
        $transfer->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $transfer->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new TransferResource($transfer);
    }
    public function destroy(Request $request, Transfer $transfer)
    {
        $transfer->delete();
        return new TransferResource($transfer);
    }
}
