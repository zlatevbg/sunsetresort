<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Building extends Model
{
    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'project_id',
    ];

    public $translatedAttributes = [
        'name', 'description',
    ];

    // protected $with = ['translations'];
}
