<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends BaseModel
{
    use HasFactory;
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public function getKey()
    {
        return 'key';
    }
}
