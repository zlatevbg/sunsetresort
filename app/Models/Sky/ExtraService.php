<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class ExtraService extends Model
{
    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'parent', 'price',
    ];

    public $translatedAttributes = [
        'name'
    ];

    // protected $with = ['translations'];

    public function setParentAttribute($value)
    {
        $this->attributes['parent'] = $value ?: null;
    }
}
