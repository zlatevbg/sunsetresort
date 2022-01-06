<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Poa extends Model
{
    use SoftDeletes;

    protected $table = 'poa';

    protected $dates = ['deleted_at', 'sent_at'];

    protected $fillable = [
        'from',
        'to',
        'apartment_id',
        'owner_id',
        'proxy_id',
    ];

    public function proxy()
    {
        return $this->belongsTo(Proxy::class);
    }

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function rentalContract()
    {
        return $this->hasOne(RentalContractTracker::class);
    }
}
