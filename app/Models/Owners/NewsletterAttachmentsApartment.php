<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachmentsApartment extends Model
{
    protected $table = 'newsletter_attachments_apartment';

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
