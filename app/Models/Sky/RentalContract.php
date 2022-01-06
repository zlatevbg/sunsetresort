<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dimsav\Translatable\Translatable;
use Carbon\Carbon;

class RentalContract extends Model
{
    use SoftDeletes, Translatable;

    protected $fillable = [
        'name', 'mm_covered', 'deadline_at', 'min_duration', 'max_duration', 'benefits', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2', 'year_id', 'rental_payment_id',
    ];

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

    public function setRentalPaymentIdAttribute($value)
    {
        $this->attributes['rental_payment_id'] = $value ?: null;
    }

    public function setDeadlineAtAttribute($value)
    {
        $this->attributes['deadline_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDeadlineAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setContractDfrom1Attribute($value)
    {
        $this->attributes['contract_dfrom1'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getContractDfrom1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setContractDfrom2Attribute($value)
    {
        $this->attributes['contract_dfrom2'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getContractDfrom2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setContractDto1Attribute($value)
    {
        $this->attributes['contract_dto1'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getContractDto1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setContractDto2Attribute($value)
    {
        $this->attributes['contract_dto2'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getContractDto2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPersonalDfrom1Attribute($value)
    {
        $this->attributes['personal_dfrom1'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPersonalDfrom1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPersonalDfrom2Attribute($value)
    {
        $this->attributes['personal_dfrom2'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPersonalDfrom2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPersonalDto1Attribute($value)
    {
        $this->attributes['personal_dto1'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPersonalDto1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setPersonalDto2Attribute($value)
    {
        $this->attributes['personal_dto2'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPersonalDto2Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
