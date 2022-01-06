<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagementCompany extends Model
{
    use SoftDeletes, Translatable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name',
    ];

    public $translatedAttributes = [
        'name'
    ];

}
