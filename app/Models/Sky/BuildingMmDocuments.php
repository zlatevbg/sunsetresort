<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class BuildingMmDocuments extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'file', 'uuid', 'extension', 'size', 'building_mm_id',
    ];

    public function mm()
    {
        return $this->belongsTo(BuildingMm::class);
    }

    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = $value ?: null;
    }

}
