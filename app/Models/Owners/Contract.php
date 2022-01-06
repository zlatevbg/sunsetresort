<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contract extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'signed_at'];

    public function contractYears()
    {
        return $this->hasMany(ContractYear::class);
    }

    public function apartments()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function rentalContract()
    {
        return $this->belongsTo(RentalContract::class)->withTrashed();
    }

    public function getSignedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
