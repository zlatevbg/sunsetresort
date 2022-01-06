<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractPayment extends Model
{
    protected $dates = ['paid_at'];

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function documents()
    {
        return $this->hasMany(ContractPaymentDocuments::class);
    }

    public function company()
    {
        return $this->hasOne(RentalCompany::class);
    }

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class);
    }

    public function owner()
    {
        return $this->hasOne(Owner::class);
    }

    public function getPaidAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
