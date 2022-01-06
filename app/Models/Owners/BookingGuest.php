<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
