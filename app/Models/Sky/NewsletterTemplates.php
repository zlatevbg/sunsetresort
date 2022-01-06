<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplates extends Model
{
    protected $fillable = [
        'subject', 'teaser', 'template', 'body', 'projects', 'buildings', 'floors', 'rooms', 'furniture', 'views', 'apartments', 'owners', 'countries', 'locale_id', 'recipients', 'signature_id',
    ];

    public function attachments()
    {
        return $this->hasMany(NewsletterTemplateAttachments::class, 'template_id')->orderBy('order');
    }

    public function images()
    {
        return $this->hasMany(NewsletterTemplateImages::class, 'template_id')->orderBy('order');
    }

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
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
