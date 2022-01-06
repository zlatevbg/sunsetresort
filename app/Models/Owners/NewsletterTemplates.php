<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplates extends Model
{
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
}
