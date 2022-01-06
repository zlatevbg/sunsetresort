<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class CommunalFeePaymentDocuments extends Model
{
    protected $table = 'communal_fees_payment_documents';

    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'communal_fees_payment_id',
    ];

    protected $dates = ['signed_at'];

    public function communalFeePayment()
    {
        return $this->belongsTo(CommunalFeePayment::class, 'communal_fees_payment_id');
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
