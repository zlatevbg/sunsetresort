<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ContractYear extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mm_for_year',
        'mm_for_years',
        'price',
        'price_tc',
        'comments',
        'contract_dfrom1',
        'contract_dto1',
        'contract_dfrom2',
        'contract_dto2',
        'personal_dfrom1',
        'personal_dto1',
        'personal_dfrom2',
        'personal_dto2',
        'is_exception',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class)->withTrashed();
    }

    public function payments()
    {
        return $this->hasMany(ContractPayment::class);
    }

    public function deductions()
    {
        return $this->hasMany(ContractDeduction::class);
    }

    public function documents()
    {
        return $this->hasMany(ContractDocuments::class);
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value ?: null;
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

}
