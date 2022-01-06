<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Furniture extends Model
{
    use Translatable;

    protected $table = 'furniture';

    public $translatedAttributes = [
        'name'
    ];
}
