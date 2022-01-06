<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $dates = [
        'created_at', 'updated_at', 'arrive_at', 'departure_at',
    ];

    public function adults()
    {
        return $this->hasMany(BookingGuest::class)->where('type', 'adult')->orderBy('order');
    }

    public function children()
    {
        return $this->hasMany(BookingGuest::class)->where('type', 'child')->orderBy('order');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class)->withTrashed();
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    public function getArriveAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDepartureAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function adultsCount()
    {
        return $this->adults()->selectRaw('count(*) as aggregate, booking_id')->groupBy('booking_id');
    }

    public function getAdultsCountAttribute()
    {
        if (!$this->relationLoaded('adultsCount')) {
            $this->load('adultsCount');
        }

        $related = $this->getRelation('adultsCount')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }

    public function childrenCount()
    {
        return $this->children()->selectRaw('count(*) as aggregate, booking_id')->groupBy('booking_id');
    }

    public function getChildrenCountAttribute()
    {
        if (!$this->relationLoaded('childrenCount')) {
            $this->load('childrenCount');
        }

        $related = $this->getRelation('childrenCount')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }
}
