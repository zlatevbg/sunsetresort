<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractPayment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'paid_at', 'comments', 'payment_method_id', 'contract_year_id', 'rental_company_id', 'owner_id',
    ];

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

    public function setPaidAtAttribute($value)
    {
        $this->attributes['paid_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getPaidAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
