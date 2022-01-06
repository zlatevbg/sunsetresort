<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'route', 'description', 'default_locale_id', 'hide_default_locale',
    ];

    /**
     * Get the locales for the domain.
     */
    public function locales()
    {
        return $this->belongsToMany(Locale::class)->withTimestamps()->orderBy('name');
    }

    /**
     * Eager loading locales count
     */
    public function localesCount()
    {
        return $this->locales()->selectRaw('count(*) as aggregate')->groupBy('domain_id');
    }

    /**
     * Accessor for easier fetching the count
     */
    public function getLocalesCountAttribute()
    {
        if (!$this->relationLoaded('localesCount')) {
            $this->load('localesCount');
        }

        $related = $this->getRelation('localesCount')->first();

        return ($related) ? (int) $related->aggregate : 0;
    }
}
