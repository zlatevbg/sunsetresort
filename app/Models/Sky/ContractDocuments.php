<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContractDocuments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'signed_at', 'file', 'uuid', 'extension', 'size', 'contract_year_id',
    ];

    protected $dates = ['signed_at'];

    public function contractYear()
    {
        return $this->belongsTo(ContractYear::class)->withTrashed();
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
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
