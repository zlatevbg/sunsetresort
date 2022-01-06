<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplateAttachments extends Model
{
    public function template()
    {
        return $this->belongsTo(NewsletterTemplates::class, 'template_id');
    }
}
