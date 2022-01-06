<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use SoftDeletes, Translatable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
    ];

    public $translatedAttributes = [
        'name'
    ];

    // protected $with = ['translations'];

    public function contractPayments()
    {
        return $this->belongsTo(ContractPayment::class);
    }
}
