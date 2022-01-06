<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class CommunalFeePaymentDocuments extends Model
{
    protected $table = 'communal_fees_payment_documents';

    protected $dates = ['signed_at'];

    public function communalFeePayment()
    {
        return $this->belongsTo(CommunalFeePayment::class, 'communal_fees_payment_id');
    }

}
