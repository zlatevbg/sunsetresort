<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterAttachments extends Model
{
    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
