<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class CommunalFeeContractTracker extends Model
{
    protected $table = 'communal_fee_contracts_tracker';

    protected $fillable = [
        'apartment_id', 'owner_id', 'year_id', 'comments',
    ];

    protected $dates = [
        'created_at', 'updated_at', 'sent_at',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartment::class);
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
