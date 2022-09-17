<?php

namespace App\Casts;

use App\Models\Setting;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Rounded implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        //some logic based on model tenant to get number of allowed digits
        // $digits=2;
        // $tenant_id = request('tenant_id');
        $digits = Setting::find('digits_number');
        $digits = $digits?$digits->value:2;
        return round($value,$digits);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, $key, $value, $attributes)
    {
        $digits = Setting::find('digits_number');
        $digits = $digits?$digits->value:2;
        return round($value,$digits);
    }
}
