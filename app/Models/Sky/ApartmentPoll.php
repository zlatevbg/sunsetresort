<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ApartmentPoll extends Model
{
    use SoftDeletes;

    protected $table = 'apartment_poll';

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'apartment_id',
        'poll_id',
        'q1',
        'q2',
    ];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

}
