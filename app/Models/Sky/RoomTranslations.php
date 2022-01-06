<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class RoomTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description',
    ];

    public $timestamps = false;
}
