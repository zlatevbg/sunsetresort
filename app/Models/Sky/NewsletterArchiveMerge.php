<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class NewsletterArchiveMerge extends Model
{
    protected $table = 'newsletter_archive_merge';

    protected $fillable = [
        'newsletter_archive_id', 'key', 'value',
    ];

    public $timestamps = false;

    public function archive()
    {
        return $this->belongsTo(NewsletterArchive::class);
    }
}
