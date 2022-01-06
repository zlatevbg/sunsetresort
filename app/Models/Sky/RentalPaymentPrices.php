<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalPaymentPrices extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rental_payment_id',
        'room_id',
        'furniture_id',
        'view_id',
        'price',
    ];

    public $timestamps = false;

    public function rentalPayment()
    {
        return $this->belongsTo(RentalPayment::class);
    }

}
