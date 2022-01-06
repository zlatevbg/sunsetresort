<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachmentsApartment extends Model
{
    protected $table = 'newsletter_attachments_apartment';

    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'order', 'newsletter_id',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
