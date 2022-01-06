<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class MmFeePaymentDocuments extends Model
{
    protected $table = 'mm_fees_payment_documents';

    protected $dates = ['signed_at'];

    public function mmFeePayment()
    {
        return $this->belongsTo(MmFeePayment::class, 'mm_fees_payment_id');
    }
}
