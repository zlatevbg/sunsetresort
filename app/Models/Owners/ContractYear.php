<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ContractYear extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'contract_dfrom1', 'contract_dto1', 'contract_dfrom2', 'contract_dto2', 'personal_dfrom1', 'personal_dto1', 'personal_dfrom2', 'personal_dto2'];

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

    public function getContractDfrom1Attribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
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
