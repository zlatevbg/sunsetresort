<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Carbon\Carbon;

class Contract extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['contractYears'];

    protected $dates = ['deleted_at', 'signed_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'apartment_id',
        'rental_contract_id',
        'duration',
        'signed_at',
        'comments',
        'is_exception',
    ];

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

    public function setSignedAtAttribute($value)
    {
        $this->attributes['signed_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getSignedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
