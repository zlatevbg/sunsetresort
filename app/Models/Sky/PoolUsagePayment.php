<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PoolUsagePayment extends Model
{
    protected $table = 'pool_usage_payments';

    protected $fillable = [
        'amount', 'paid_at', 'comments', 'payment_method_id', 'apartment_id', 'year_id', 'rental_company_id', 'owner_id',
    ];

    protected $dates = ['paid_at'];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function documents()
    {
        return $this->hasMany(PoolUsagePaymentDocuments::class, 'pool_usage_payment_id');
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

    public function setPaidAtAttribute($value)
    {
        $this->attributes['paid_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPaidAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setRentalCompanyIdAttribute($value)
    {
        $this->attributes['rental_company_id'] = $value ?: null;
    }

    public function setOwnerIdAttribute($value)
    {
        $this->attributes['owner_id'] = $value ?: null;
    }

}
