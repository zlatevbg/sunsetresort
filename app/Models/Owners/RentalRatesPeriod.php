<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RentalRatesPeriod extends Model
{
    protected $dates = ['deleted_at'];

    public function rates()
    {
        return $this->hasMany(RentalRates::class, 'period_id');
    }

    public function getDfromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDfromWithoutYearAttribute($value)
    {
        return $this->attributes['dfrom'] ? Carbon::parse($this->attributes['dfrom'])->format('d.m') : null;
    }

    public function getDtoAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDtoWithoutYearAttribute($value)
    {
        return $this->attributes['dto'] ? Carbon::parse($this->attributes['dto'])->format('d.m') : null;
    }
}
