<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterImages extends Model
{
    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
