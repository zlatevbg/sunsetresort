<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterArchive extends Model
{
    protected $table = 'newsletter_archive';

    public $timestamps = false;

    public function merge()
    {
        return $this->hasMany(NewsletterArchiveMerge::class);
    }

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
