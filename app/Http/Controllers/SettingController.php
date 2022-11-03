<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{

    public static function routeName()
    {
        return Str::snake("Setting");
    }
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->authorizeResource(Setting::class, Str::snake("Setting"));
    }
    public function index(Request $request)
    {
        // $first = 'im the one ';
        // $second = 'first';
        // $third = 'second';
        // dd($$$third);  
        return SettingResource::collection(Setting::search($request)->sort($request)->paginate($this->pagination));
    }
    public function store(StoreSettingRequest $request)
    {
        $data = $request->validated();
        // return $data;
        foreach ($data as  $value) {
            // return ($value);
            $setting = Setting::updateOrCreate(['key' => $value['key']], ['value' => $value['value']]);
        }
        // dd($data);

        // if ($request->translations) {
        //     foreach ($request->translations as $translation)
        //         $setting->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        // }
        return new SettingResource($setting);
    }
    public function show(Request $request, Setting $setting)
    {
        return new SettingResource($setting);
    }
    public function update(UpdateSettingRequest $request, Setting $setting)
    {
        $setting->update($request->validated());
        if ($request->translations) {
            foreach ($request->translations as $translation)
                $setting->setTranslation($translation['field'], $translation['locale'], $translation['value'])->save();
        }
        return new SettingResource($setting);
    }
    public function destroy(Request $request, Setting $setting)
    {
        $setting->delete();
        return new SettingResource($setting);
    }
}
