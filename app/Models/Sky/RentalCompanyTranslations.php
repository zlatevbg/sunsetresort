<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class RentalCompanyTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'address', 'manager',
    ];

    public $timestamps = false;
}
