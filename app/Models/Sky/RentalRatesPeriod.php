<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Carbon\Carbon;

class RentalRatesPeriod extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['rates'];

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'dfrom',
        'dto',
        'type',
    ];

    public function rates()
    {
        return $this->hasMany(RentalRates::class, 'period_id');
    }

    public function setDfromAttribute($value)
    {
        $this->attributes['dfrom'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDfromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDfromWithoutYearAttribute($value)
    {
        return $this->attributes['dfrom'] ? Carbon::parse($this->attributes['dfrom'])->format('d.m') : null;
    }

    public function setDtoAttribute($value)
    {
        $this->attributes['dto'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDtoAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDtoWithoutYearAttribute($value)
    {
        return $this->attributes['dto'] ? Carbon::parse($this->attributes['dto'])->format('d.m') : null;
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }
}
