<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class BuildingMmDocuments extends Model
{
    public function mm()
    {
        return $this->belongsTo(BuildingMm::class);
    }

}
