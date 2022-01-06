<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class CondominiumDocuments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'condominium_id',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
