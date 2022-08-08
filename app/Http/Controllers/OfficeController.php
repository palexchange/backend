<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreOfficeRequest;
use App\Http\Requests\UpdateOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class OfficeController extends Controller
{

    public static function routeName(){
        return Str::snake("Office");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(Office::class,Str::snake("Office"));
    }
    public function index(Request $request)
    {
        return OfficeResource::collection(Office::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreOfficeRequest $request)
    {
        $office = Office::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $office->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new OfficeResource($office);
    }
    public function show(Request $request,Office $office)
    {
        return new OfficeResource($office);
    }
    public function update(UpdateOfficeRequest $request, Office $office)
    {
        $office->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $office->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new OfficeResource($office);
    }
    public function destroy(Request $request,Office $office)
    {
        $office->delete();
        return new OfficeResource($office);
    }
}
