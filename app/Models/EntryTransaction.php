<?php

namespace App\Models;

use App\Casts\Rounded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Current;

class EntryTransaction extends BaseModel
{
    use HasFactory;
    protected $appends = ['account_name', 'currency_name'];
    protected $casts = [
        'ac_debtor' => Rounded::class,
        'ac_creditor' => Rounded::class,
        'debtor' => Rounded::class,
        'creditor' => Rounded::class,

    ];
    public function scopeSearch($query, $request)
    {
        $query->when($request->entry_id,  function ($query, $entry_id) {
            $query->where('entry_id', $entry_id);
        });
    }
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function getAccountNameAttribute()
    {
        return $this->account()->first('name')->name;
    }
    public function getCurrencyNameAttribute()
    {
        return $this->currency()->first('name')->name;
    }
}
