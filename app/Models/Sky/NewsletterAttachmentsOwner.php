<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachmentsOwner extends Model
{
    protected $table = 'newsletter_attachments_owner';

    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'order', 'newsletter_id',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
