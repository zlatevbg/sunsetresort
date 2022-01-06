<?php

namespace App\Models\Owners;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Authenticatable
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function notices()
    {
        return $this->belongsToMany(Notice::class)->where('is_active', 1)->withTimestamps();
    }

    public function ownership()
    {
        return $this->hasMany(Ownership::class);
    }

    public function apartments()
    {
        return $this->hasMany(Ownership::class)->with('apartment');
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class)->withTimestamps();
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }
}
