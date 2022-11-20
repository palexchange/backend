<?php

namespace App\Http\Controllers;

use App\Models\InventoryLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\InventoryLogResource;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInventoryLogRequest;
use App\Http\Requests\UpdateInventoryLogRequest;

use Illuminate\Support\Facades\Validator;

class InventoryLogController extends Controller
{

    public static function routeName()
    {
        return Str::snake("InventoryLog");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(InventoryLog::class, Str::snake("InventoryLog"));
    }
    public function index(Request $request)
    {
        return InventoryLogResource::collection(InventoryLog::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreInventoryLogRequest $request)
    {
        $value = json_encode($request->validated()['value']);
        $headers = json_encode($request->validated()['headers']);


        $inventoryLog = InventoryLog::create(["value" => $value, "headers" => $headers]);
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $inventoryLog->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new InventoryLogResource($inventoryLog);
    }
    public function show(Request $request, InventoryLog $inventoryLog)
    {
        return new InventoryLogResource($inventoryLog);
    }
    public function update(UpdateInventoryLogRequest $request, InventoryLog $inventoryLog)
    {
        $inventoryLog->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $inventoryLog->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new InventoryLogResource($inventoryLog);
    }
    public function destroy(Request $request, InventoryLog $inventoryLog)
    {
        $inventoryLog->delete();
        return new InventoryLogResource($inventoryLog);
    }
}
