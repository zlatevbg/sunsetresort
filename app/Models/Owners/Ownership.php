<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Ownership extends Model
{
    use SoftDeletes;

    protected $table = 'ownership';

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
