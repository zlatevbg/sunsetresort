<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class AgentAccess extends Model
{
    use SoftDeletes;

    protected $table = 'agent_access';

    protected $dates = ['deleted_at', 'dfrom', 'dto'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'apartment_id',
        'agent_id',
        'dfrom',
        'dto',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function getDfromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setDfromAttribute($value)
    {
        $this->attributes['dfrom'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDtoAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setDtoAttribute($value)
    {
        $this->attributes['dto'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDeletedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

}
