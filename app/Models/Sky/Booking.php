<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'project_id', 'building_id', 'apartment_id', 'owner_id', 'services', /*'kitchen_items', 'loyalty_card', 'club_card', */'exception', /*'deposit_paid', 'hotel_card', */'arrive_at', 'departure_at', 'arrival_time', 'departure_time', 'arrival_airport_id', 'departure_airport_id', 'arrival_flight', 'departure_flight', 'arrival_transfer', 'departure_transfer', 'message', 'comments', 'accommodation_costs', 'transfer_costs', 'services_costs',
    ];

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

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    public function setServicesAttribute($value)
    {
        $this->attributes['services'] = $value ? implode(',', $value) : null;
    }

    /*public function setKitchenItemsAttribute($value)
    {
        $this->attributes['kitchen_items'] = is_numeric($value) ? $value : null;
    }

    public function setLoyaltyCardAttribute($value)
    {
        $this->attributes['loyalty_card'] = is_numeric($value) ? $value : null;
    }

    public function setClubCardAttribute($value)
    {
        $this->attributes['club_card'] = is_numeric($value) ? $value : null;
    }*/

    public function setExceptionAttribute($value)
    {
        $this->attributes['exception'] = is_numeric($value) ? $value : null;
    }

    /*public function setDepositPaidAttribute($value)
    {
        $this->attributes['deposit_paid'] = is_numeric($value) ? $value : null;
    }

    public function setHotelCardAttribute($value)
    {
        $this->attributes['hotel_card'] = is_numeric($value) ? $value : null;
    }*/

    public function setArrivalAirportIdAttribute($value)
    {
        $this->attributes['arrival_airport_id'] = $value ?: null;
    }

    public function setDepartureAirportIdAttribute($value)
    {
        $this->attributes['departure_airport_id'] = $value ?: null;
    }

    public function setArrivalTimeAttribute($value)
    {
        $this->attributes['arrival_time'] = $value ?: null;
    }

    public function setDepartureTimeAttribute($value)
    {
        $this->attributes['departure_time'] = $value ?: null;
    }

    public function setArrivalFlightAttribute($value)
    {
        $this->attributes['arrival_flight'] = $value ?: null;
    }

    public function setDepartureFlightAttribute($value)
    {
        $this->attributes['departure_flight'] = $value ?: null;
    }

    public function setArrivalTransferAttribute($value)
    {
        $this->attributes['arrival_transfer'] = $value ?: null;
    }

    public function setDepartureTransferAttribute($value)
    {
        $this->attributes['departure_transfer'] = $value ?: null;
    }

    public function setArriveAtAttribute($value)
    {
        $this->attributes['arrive_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getArriveAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setDepartureAtAttribute($value)
    {
        $this->attributes['departure_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDepartureAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = $value ?: null;
    }

    public function setCommentsAttribute($value)
    {
        $this->attributes['comments'] = $value ?: null;
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
