<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachmentsOwner extends Model
{
    protected $table = 'newsletter_attachments_owner';

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
