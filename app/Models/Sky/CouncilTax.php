<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CouncilTax extends Model
{
    protected $table = 'council_tax';

    protected $fillable = [
        'tax', 'checked_at', 'owner_id', 'apartment_id',
    ];

    protected $dates = [
        'checked_at',
    ];

    public function setCheckedAtAttribute($value)
    {
        $this->attributes['checked_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getCheckedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setTaxAttribute($value)
    {
        $this->attributes['tax'] = $value ?: null;
    }
}
