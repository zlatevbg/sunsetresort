<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class ContractPaymentDocuments extends Model
{
    protected $dates = ['signed_at'];

    public function contractPayment()
    {
        return $this->belongsTo(ContractPayment::class);
    }

}
