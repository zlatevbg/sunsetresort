<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class PoolUsagePaymentDocuments extends Model
{
    protected $table = 'pool_usage_payment_documents';

    protected $dates = ['signed_at'];

    public function poolUsagePayment()
    {
        return $this->belongsTo(PoolUsagePayment::class, 'pool_usage_payment_id');
    }

}
