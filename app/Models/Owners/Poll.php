<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Carbon\Carbon;

class Poll extends Model
{
    use Translatable;

    public $translatedAttributes = [
        'name', 'content',
    ];

    protected $dates = [
        'dfrom', 'dto',
    ];

    // protected $with = ['translations'];

    public function votes()
    {
        return $this->belongsToMany(Apartment::class)->withTimestamps()->orderBy('number');
    }

    /**
     * Eager loading locales count
     */
    public function votesCount()
    {
        return $this->votes()->selectRaw('count(*) as aggregate')->groupBy('poll_id');
    }

    /**
     * Accessor for easier fetching the count
     */
    public function getVotesCountAttribute()
    {
        if (!$this->relationLoaded('votesCount')) {
            $this->load('votesCount');
        }

        $related = $this->getRelation('votesCount')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }

    public function getDfromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function getDtoAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
