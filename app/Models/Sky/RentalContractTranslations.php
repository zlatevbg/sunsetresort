<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class RentalContractTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'benefits',
    ];

    public $timestamps = false;
}
