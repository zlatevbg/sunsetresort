<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Furniture extends Model
{
    use Translatable;

    protected $table = 'furniture';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public $translatedAttributes = [
        'name'
    ];

    // protected $with = ['translations'];
}
