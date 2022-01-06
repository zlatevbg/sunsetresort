<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;
use Carbon\Carbon;

class Poll extends Model
{
    use Translatable, SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['votes'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content', 'dfrom', 'dto',
    ];

    public $translatedAttributes = [
        'name', 'content',
    ];

    protected $dates = [
        'dfrom', 'dto', 'deleted_at',
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

    public function setDfromAttribute($value)
    {
        $this->attributes['dfrom'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDfromAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }

    public function setDtoAttribute($value)
    {
        $this->attributes['dto'] = $value ? Carbon::parse($value)->toDateTimeString() : null;
    }

    public function getDtoAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
