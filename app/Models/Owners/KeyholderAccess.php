<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class KeyholderAccess extends Model
{
    use SoftDeletes;

    protected $table = 'keyholder_access';

    protected $dates = ['deleted_at'];

    public function keyholder()
    {
        return $this->belongsTo(Keyholder::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
