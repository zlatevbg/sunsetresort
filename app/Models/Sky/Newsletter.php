<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $fillable = [
        'subject', 'teaser', 'body', 'projects', 'buildings', 'floors', 'rooms', 'furniture', 'views', 'apartments', 'owners', 'countries', 'locale_id', 'year_id', 'recipients', 'merge_by', 'signature_id', 'template',
    ];

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

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function merge()
    {
        return $this->hasMany(NewsletterMerge::class)->orderBy('order');
    }

    public function setMergeByAttribute($value)
    {
        $this->attributes['merge_by'] = $value ?: null;
    }

    public function setProjectsAttribute($value)
    {
        $this->attributes['projects'] = $value ? implode(',', $value) : null;
    }

    public function setBuildingsAttribute($value)
    {
        $this->attributes['buildings'] = $value ? implode(',', $value) : null;
    }

    public function setFloorsAttribute($value)
    {
        $this->attributes['floors'] = $value ? implode(',', $value) : null;
    }

    public function setRoomsAttribute($value)
    {
        $this->attributes['rooms'] = $value ? implode(',', $value) : null;
    }

    public function setFurnitureAttribute($value)
    {
        $this->attributes['furniture'] = $value ? implode(',', $value) : null;
    }

    public function setViewsAttribute($value)
    {
        $this->attributes['views'] = $value ? implode(',', $value) : null;
    }

    public function setApartmentsAttribute($value)
    {
        $this->attributes['apartments'] = $value ? implode(',', $value) : null;
    }

    public function setOwnersAttribute($value)
    {
        $this->attributes['owners'] = $value ? implode(',', $value) : null;
    }

    public function setCountriesAttribute($value)
    {
        $this->attributes['countries'] = $value ? implode(',', $value) : null;
    }

    public function setRecipientsAttribute($value)
    {
        $this->attributes['recipients'] = $value ? implode(',', $value) : null;
    }
}
