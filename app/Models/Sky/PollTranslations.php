<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class PollTranslations extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content',
    ];

    public $timestamps = false;
}
