<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreCountryRequest;
use App\Http\Requests\UpdateCountryRequest;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{

    public static function routeName(){
        return Str::snake("Country");
    }
    public function __construct(Request $request){
        parent::__construct($request);
        $this->authorizeResource(Country::class,Str::snake("Country"));
    }
    public function index(Request $request)
    {
        return CountryResource::collection(Country::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreCountryRequest $request)
    {
        $country = Country::create($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $country->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CountryResource($country);
    }
    public function show(Request $request,Country $country)
    {
        return new CountryResource($country);
    }
    public function update(UpdateCountryRequest $request, Country $country)
    {
        $country->update($request->validated());
          if ($request->translations) {
            foreach ($request->translations as $translation)
                $country->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new CountryResource($country);
    }
    public function destroy(Request $request,Country $country)
    {
        $country->delete();
        return new CountryResource($country);
    }
}
