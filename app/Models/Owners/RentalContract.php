<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class RentalContract extends Model
{
    use SoftDeletes, Translatable;

    public $translatedAttributes = [
        'name', 'benefits',
    ];

    // protected $with = ['translations'];

    protected $dates = [
        'deleted_at', 'deadline_at', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2',
    ];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function rentalPayment()
    {
        return $this->belongsTo(RentalPayment::class);
    }

    public function getDeadlineAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getContractDfrom1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getContractYearAttribute()
    {
        return Carbon::parse($this->attributes['contract_dfrom1'])->year;
    }

    public function getContractDfrom2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getContractDto1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getContractDto2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getPersonalDfrom1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getPersonalDfrom2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getPersonalDto1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getPersonalDto2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
