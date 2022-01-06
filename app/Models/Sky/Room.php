<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Room extends Model
{
    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'capacity',
    ];

    public $translatedAttributes = [
        'name', 'description',
    ];

    // protected $with = ['translations'];
}
