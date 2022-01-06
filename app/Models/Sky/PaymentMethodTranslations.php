<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
