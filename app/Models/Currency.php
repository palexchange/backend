<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends BaseModel
{
    use HasFactory;
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'currency_id', 'id');
    }
}
