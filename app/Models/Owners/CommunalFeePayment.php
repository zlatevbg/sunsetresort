<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CommunalFeePayment extends Model
{
    protected $table = 'communal_fees_payments';

    protected $dates = ['paid_at'];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function documents()
    {
        return $this->hasMany(CommunalFeePaymentDocuments::class, 'communal_fees_payment_id');
    }

    public function company()
    {
        return $this->hasOne(RentalCompany::class);
    }

    public function paymentMethod()
    {
        return $this->hasOne(PaymentMethod::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
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
