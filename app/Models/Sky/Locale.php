<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale', 'name', 'description', 'native', 'script',
    ];

    /**
     * Get the domains using the locale.
     */
    public function domains()
    {
        return $this->belongsToMany(Domain::class)->withTimestamps();
    }
}
