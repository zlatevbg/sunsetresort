<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class KeyLog extends Model
{
    protected $table = 'key_log';

    protected $dates = ['occupied_at'];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getOccupiedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
