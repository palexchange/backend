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
        $query->when($request->name, function ($q, $name) {
            $q->where('name', 'LIKE', "%{$name}%");
        });
        $query->when($request->id_no, function ($q, $id_no) {
            $q->where('id_no', 'LIKE', "%{$id_no}%");
        });
        $query->when($request->phone, function ($q, $phone) {
            $q->where('phone', 'LIKE', "%{$phone}%");
        });
    }
    public function image()
    {
        return $this->morphOne(File::class, 'attachable');
    }
}
