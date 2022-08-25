<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiary extends BaseModel
{
    use HasFactory;
    public function scopeSort($query, $request)
    {
    }
    public function scopeSearch($query, $request)
    {
    }
}
