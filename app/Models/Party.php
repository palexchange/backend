<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use function PHPUnit\Framework\isEmpty;

class Party extends BaseModel
{
    use HasFactory;
    protected $with = ['image'];
    protected $appends = ['received_money_gram_count', 'sended_money_gram_count'];

    public function getSendedMoneyGramCountAttribute()
    {
        return Transfer::where('sender_party_id', $this->id)
            ->where("delivering_type", 2)
            ->where("type", 0)
            ->where("status", 1)
            ->count();
    }
    public function getReceivedMoneyGramCountAttribute()
    {

        return Transfer::where('receiver_party_id', $this->id)
            ->where("delivering_type", 2)
            ->where("type", 1)
            ->where("status", 1)
            ->count();
    }

    public function scopeSort($query, $request)
    {
        $sortBy = $request->sortBy;
        $sortDesc = $request->sortDesc;
        $custom_fields = [];
        if (!$sortBy && !$sortDesc)
            $query->orderby('id', 'desc');
        if ($sortBy && $sortDesc) {
            foreach ($sortBy as $index => $field) {
                $desc = $sortDesc[$index] == 'true' ? "desc" : "asc";
                if (!isset($custom_fields[$field])) {
                    $query->orderBy($field, $desc);
                } else {
                    $custom_fields[$field]($query, $desc);
                }
            }
        }
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
