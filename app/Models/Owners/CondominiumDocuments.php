<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class CondominiumDocuments extends Model
{
    public function mm()
    {
        return $this->belongsTo(Condominium::class);
    }

}
