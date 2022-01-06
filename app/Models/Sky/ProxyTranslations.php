<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class ProxyTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'address', 'issued_by',
    ];

    public $timestamps = false;

    public function setIssuedByAttribute($value)
    {
        $this->attributes['issued_by'] = $value ?: null;
    }
}
