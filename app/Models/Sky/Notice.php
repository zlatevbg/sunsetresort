<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    protected $fillable = [
        'name', 'content', 'auto_assign', 'locale_id',
    ];

    public function owners()
    {
        return $this->belongsToMany(Owner::class)->withTimestamps();
    }

    /**
     * Eager loading owners count
     */
    public function ownersCount()
    {
        return $this->owners()->selectRaw('count(*) as aggregate')->groupBy('notice_id');
    }

    /**
     * Accessor for easier fetching the count
     */
    public function getOwnersCountAttribute()
    {
        if (!$this->relationLoaded('ownersCount')) {
            $this->load('ownersCount');
        }

        $related = $this->getRelation('ownersCount')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }

    /**
     * Eager loading owners count
     */
    public function ownersRead()
    {
        return $this->owners()->selectRaw('count(*) as aggregate')->where('is_read', 1)->groupBy('notice_id');
    }

    /**
     * Accessor for easier fetching the count
     */
    public function getOwnersReadAttribute()
    {
        if (!$this->relationLoaded('ownersRead')) {
            $this->load('ownersRead');
        }

        $related = $this->getRelation('ownersRead')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }
}
