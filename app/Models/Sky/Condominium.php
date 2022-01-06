<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Condominium extends Model
{
    protected $table = 'condominium';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'assembly_at', 'building_id', 'year_id',
    ];

    protected $dates = [
        'assembly_at',
    ];

    public function documents()
    {
        return $this->hasMany(CondominiumDocuments::class);
    }

    public function setAssemblyAtAttribute($value)
    {
        $this->attributes['assembly_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getAssemblyAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
