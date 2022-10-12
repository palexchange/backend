<?php

namespace App\Http\Controllers;

use App\Events\DocumentStoredEvent;
use App\Http\Requests\StoreReceiptRequest;
use App\Http\Requests\UpdateReceiptRequest;
use App\Http\Resources\ReceiptResource;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class ReceiptController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Receipt");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Receipt::class, Str::snake("Receipt"));
    }
    public function index(Request $request)
    {
        return ReceiptResource::collection(Receipt::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreReceiptRequest $request)
    {
        $receipt = Receipt::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $receipt->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        DocumentStoredEvent::dispatch($receipt);
        return new ReceiptResource($receipt);
    }
    public function show(Request $request, Receipt $receipt)
    {
        return new ReceiptResource($receipt);
    }
    public function update(UpdateReceiptRequest $request, Receipt $receipt)
    {
        $receipt->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $receipt->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new ReceiptResource($receipt);
    }
    public function destroy(Request $request, Receipt $receipt)
    {
        $receipt->delete();
        return new ReceiptResource($receipt);
    }
}
