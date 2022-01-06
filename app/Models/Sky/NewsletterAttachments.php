<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachments extends Model
{
    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'order', 'newsletter_id',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
