<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Proxy extends Model
{
    use SoftDeletes, Translatable;

    protected $dates = ['deleted_at', 'issued_at'];

    protected $fillable = [
        'name', 'egn', 'id_card', 'issued_at', 'address', 'issued_by', 'is_company', 'bulstat',
    ];

    public $translatedAttributes = [
        'name', 'address', 'issued_by',
    ];

    // protected $with = ['translations'];

    public function setIssuedAtAttribute($value)
    {
        $this->attributes['issued_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getIssuedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
