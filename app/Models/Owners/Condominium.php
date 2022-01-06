<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Condominium extends Model
{
    protected $table = 'condominium';

    protected $dates = [
        'assembly_at',
    ];

    public function documents()
    {
        return $this->hasMany(CondominiumDocuments::class);
    }

    public function getAssemblyAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
