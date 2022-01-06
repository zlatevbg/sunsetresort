<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class RentalPayment extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['rentalPaymentPrices'];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public function rentalContracts()
    {
        return $this->hasMany(RentalContract::class);
    }

    public function rentalPaymentPrices()
    {
        return $this->hasMany(RentalPaymentPrices::class);
    }
}
