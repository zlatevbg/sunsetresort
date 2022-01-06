<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class ContractPaymentDocuments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'contract_payment_id',
    ];

    protected $dates = ['signed_at'];

    public function contractPayment()
    {
        return $this->belongsTo(ContractPayment::class);
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
