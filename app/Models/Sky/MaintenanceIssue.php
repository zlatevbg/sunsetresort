<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MaintenanceIssue extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_issues';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'comments',
        'status',
        'responsibility',
        'apartment_id',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getUpdatedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
