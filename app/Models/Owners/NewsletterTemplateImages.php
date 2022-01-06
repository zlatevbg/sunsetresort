<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplateImages extends Model
{
    public function template()
    {
        return $this->belongsTo(NewsletterTemplates::class, 'template_id');
    }
}
