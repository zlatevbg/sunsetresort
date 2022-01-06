<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class ViewTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public $timestamps = false;
}
