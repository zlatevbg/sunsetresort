<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplateImages extends Model
{
    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'order', 'template_id',
    ];

    public function template()
    {
        return $this->belongsTo(NewsletterTemplates::class, 'template_id');
    }
}
