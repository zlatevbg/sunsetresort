<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Apartment extends Model
{
    use SoftDeletes;

    public function ownership()
    {
        return $this->hasMany(Ownership::class);
    }

    public function owners()
    {
        return $this->hasMany(Ownership::class)->with('owner');
    }

    public function allowners()
    {
        return $this->hasMany(Ownership::class)->with(['owner' => function ($query) {
            $query->withTrashed();
        }]);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function agents()
    {
        return $this->hasMany(AgentAccess::class);
    }

    public function keyholders()
    {
        return $this->hasMany(KeyholderAccess::class)->withTrashed();
    }

    public function mmFeesPayments()
    {
        return $this->hasMany(MmFeePayment::class);
    }

    public function communalFeesPayments()
    {
        return $this->hasMany(CommunalFeePayment::class);
    }

    public function poolUsagePayments()
    {
        return $this->hasMany(PoolUsagePayment::class);
    }

    public function poolUsageContracts()
    {
        return $this->hasMany(PoolUsageContractTracker::class);
    }

    public function buildingMM()
    {
        return $this->hasMany(BuildingMm::class, 'building_id', 'building_id');
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function rooms()
    {
        // for calculateMmFees && calculateRentalOptions
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function furniture()
    {
        return $this->belongsTo(Furniture::class);
    }

    public function view()
    {
        return $this->belongsTo(View::class);
    }
}
