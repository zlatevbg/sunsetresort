<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class MmFeePaymentDocuments extends Model
{
    protected $table = 'mm_fees_payment_documents';

    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'mm_fees_payment_id',
    ];

    protected $dates = ['signed_at'];

    public function mmFeePayment()
    {
        return $this->belongsTo(MmFeePayment::class, 'mm_fees_payment_id');
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
