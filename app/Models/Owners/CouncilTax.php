<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CouncilTax extends Model
{
    protected $table = 'council_tax';

    protected $dates = [
        'checked_at',
    ];

    public function getCheckedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
