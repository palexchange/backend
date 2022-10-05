<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAccount extends BaseModel
{
    use HasFactory;
    protected $guarded = [];

    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
        $query->when($request->user_id, function ($query, $user_id) {
            $query->where('user_id', $user_id);
        });
    }
}
