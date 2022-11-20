<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLog extends BaseModel
{
    use HasFactory;
    public function scopeSort($query, $request)
    {
        $query->when($request->from, function ($query) use ($request) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        });
    }
    public function scopeSearch($query, $request)
    {
    }
}
