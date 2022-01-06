<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class KeyLog extends Model
{
    use SoftDeletes;

    protected $table = 'key_log';

    protected $dates = ['occupied_at'];

    protected $fillable = [
        'occupied_at',
        'apartment_id',
        'people',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function setOccupiedAtAttribute($value)
    {
        $this->attributes['occupied_at'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getOccupiedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
