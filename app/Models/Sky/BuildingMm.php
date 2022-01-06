<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BuildingMm extends Model
{
    protected $table = 'building_mm';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'management_company_id', 'mm_tax', 'deadline_at', 'building_id', 'year_id',
    ];

    protected $dates = [
        'deadline_at',
    ];

    public function documents()
    {
        return $this->hasMany(BuildingMmDocuments::class);
    }

    public function company()
    {
        return $this->hasOne(ManagementCompany::class, 'id', 'management_company_id');
    }

    public function setDeadlineAtAttribute($value)
    {
        $this->attributes['deadline_at'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDeadlineAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
