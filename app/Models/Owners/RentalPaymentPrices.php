<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class RentalPaymentPrices extends Model
{
    protected $dates = ['deleted_at'];

    public $timestamps = false;

    public function rentalPayment()
    {
        return $this->belongsTo(RentalPayment::class);
    }

}
