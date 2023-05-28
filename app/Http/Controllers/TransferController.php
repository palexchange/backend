<?php

namespace App\Http\Controllers;

use App\Events\DocumentDeletedEvent;
use App\Events\DocumentStoredEvent;
use App\Events\DocumentUpdatedEvent;
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
        return TransferResource::collection(Transfer::without(['receiver_party', 'sender_party', 'image', 'office', 'user'])->Search($request)->Sort($request)->paginate($this->pagination));
    }
    public function store(StoreTransferRequest $request)
    {
        if (!hasAbilityToCreateModelInCurrency($request->validated()['reference_currency_id']))
            return response()->json(['message' => [__('u dont have an account to complete the proceess')]], 422);


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

        if ($request->translations) {
            foreach ($request->translations as $translation)
                $transfer->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        if ($transfer->status == 1) {
            $transfer->dispose();
            $transfer->update($request->validated());
            $transfer = $transfer->fresh();
        }
        DocumentStoredEvent::dispatch($transfer);
        return new TransferResource($transfer);
    }
    public function destroy(Request $request, Transfer $transfer)
    {
        if ($transfer->status != 1) return new TransferResource($transfer);
        DocumentDeletedEvent::dispatch($transfer);
        $transfer->status = 255;
        $transfer->save();
        // $transfer->delete();
        return new TransferResource($transfer);
    }
}
