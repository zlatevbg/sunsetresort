<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class PoolUsageContractTracker extends Model
{
    protected $table = 'pool_usage_contracts_tracker';

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
