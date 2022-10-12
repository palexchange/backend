<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntryTransaction extends BaseModel
{
    use HasFactory;
    protected $appends = ['account_name'];
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
    public function getAccountNameAttribute()
    {
        return $this->account()->first('name')->name;
    }
}
