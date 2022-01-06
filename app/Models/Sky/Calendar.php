<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Calendar extends Model
{
    protected $table = 'calendar';

    protected $dates = ['date'];

    protected $fillable = [
        'date',
        'description',
    ];

    public function admins()
    {
        return $this->belongsToMany(Admin::class)->withTimestamps()->orderBy('name');
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDateAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
