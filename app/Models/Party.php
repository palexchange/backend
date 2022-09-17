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
    }
    public function image()
    {
        return $this->morphOne(File::class, 'attachable');
    }
}
