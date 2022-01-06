<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractDeduction extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'signed_at', 'comments', 'deduction_id', 'contract_year_id',
    ];

    protected $dates = ['signed_at'];

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function deduction()
    {
        return $this->hasOne(Deduction::class);
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
