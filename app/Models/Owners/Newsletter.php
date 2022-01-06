<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $dates = [
        'created_at', 'updated_at', 'sent_at',
    ];

    public function archives()
    {
        return $this->hasMany(NewsletterArchive::class);
    }

    public function attachments()
    {
        return $this->hasMany(NewsletterAttachments::class)->orderBy('order');
    }

    public function attachmentsApartment()
    {
        return $this->hasMany(NewsletterAttachmentsApartment::class)->orderBy('order');
    }

    public function attachmentsOwner()
    {
        return $this->hasMany(NewsletterAttachmentsOwner::class)->orderBy('order');
    }

    public function images()
    {
        return $this->hasMany(NewsletterImages::class)->orderBy('order');
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function merge()
    {
        return $this->hasMany(NewsletterMerge::class)->orderBy('order');
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }
}
