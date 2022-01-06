<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class ManagementCompany extends Model
{
    use Translatable;

    protected $dates = ['deleted_at'];

    public $translatedAttributes = [
        'name'
    ];

}
