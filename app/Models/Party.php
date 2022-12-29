<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends BaseModel
{
    use HasFactory;
    protected $with = ['image'];
    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
        $query->when($request->party_ids, function ($q, $party_ids) {
            $q->whereIn('id', $party_ids);
        });
    }
    public function image()
    {
        return $this->morphOne(File::class, 'attachable');
    }
}
