<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{

    public static function routeName(){
        return Str::snake("City");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(City::class,Str::snake("City"));
    }
    public function index(Request $request)
    {
        return CityResource::collection(City::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $city->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CityResource($city);
    }
    public function show(Request $request,City $city)
    {
        return new CityResource($city);
    }
    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $city->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CityResource($city);
    }
    public function destroy(Request $request,City $city)
    {
        $city->delete();
        return new CityResource($city);
    }
}
