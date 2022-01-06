<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class NewsletterMerge extends Model
{
    protected $table = 'newsletter_merge';

    public $timestamps = false;

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
