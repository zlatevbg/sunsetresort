<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalCompany extends Model
{
    use SoftDeletes, Translatable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'egn', 'id_card', 'address', 'bulstat', 'manager',
    ];

    public $translatedAttributes = [
        'name', 'address', 'manager',
    ];

    // protected $with = ['translations'];

    public function contractPayments()
    {
        return $this->belongsTo(ContractPayment::class);
    }
}
