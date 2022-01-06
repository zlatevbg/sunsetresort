<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Airport extends Model
{
    use Translatable;

    protected $dates = ['deleted_at'];

    public $translatedAttributes = [
        'name'
    ];
}
