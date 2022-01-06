<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class PoolUsagePaymentDocuments extends Model
{
    protected $table = 'pool_usage_payment_documents';

    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'pool_usage_payment_id',
    ];

    protected $dates = ['signed_at'];

    public function poolUsagePayment()
    {
        return $this->belongsTo(PoolUsagePayment::class, 'pool_usage_payment_id');
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
