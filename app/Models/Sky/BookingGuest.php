<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    protected $fillable = [
        'name', 'order', 'type', 'booking_id',
    ];

    public $timestamps = false;

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
