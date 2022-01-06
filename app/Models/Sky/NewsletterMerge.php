<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterMerge extends Model
{
    protected $table = 'newsletter_merge';

    protected $fillable = [
        'merge', 'order', 'newsletter_id',
    ];

    public $timestamps = false;

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }
}
