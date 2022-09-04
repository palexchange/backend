<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class File extends BaseModel
{
    use HasFactory;
    protected $appends = ['url'];
    public function getUrlAttribute()
    {
        return Config::get('app.url') . Storage::url($this->path);
    }
}
