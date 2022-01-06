<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RentalContractTracker extends Model
{
    protected $table = 'rental_contracts_tracker';

    protected $fillable = [
        'apartment_id', 'owner_id', 'rental_contract_id', 'poa_id', 'price', 'price_tc', 'duration', 'mm_for_year', 'mm_for_years', 'is_exception', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2', 'comments',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'sent_at', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function poa()
    {
        return $this->belongsTo(Poa::class);
    }

    public function rentalContract()
    {
        return $this->belongsTo(RentalContract::class);
    }

    public function setPriceTcAttribute($value)
    {
        $this->attributes['price_tc'] = $value ?: null;
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

    public function setMmForYearsAttribute($value)
    {
        $this->attributes['mm_for_years'] = $value ? implode(',', $value) : null;
    }
}
