<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class ExtraService extends Model
{
    use Translatable;

    public $translatedAttributes = [
        'name'
    ];
}
