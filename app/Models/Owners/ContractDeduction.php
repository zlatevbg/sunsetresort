<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractDeduction extends Model
{
    protected $dates = ['signed_at'];

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class);
    }

    public function deduction()
    {
        return $this->hasOne(Deduction::class);
    }

    public function getSignedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
