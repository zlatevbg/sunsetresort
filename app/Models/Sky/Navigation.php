<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    protected $table = 'navigation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'title', 'slug', 'route', 'route_method', 'description', 'content', 'is_category', 'is_popup', 'order', 'parent', 'type', 'locale_id',
    ];

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

    public function setParentAttribute($value)
    {
        $this->attributes['parent'] = $value ?: null;
    }
}
