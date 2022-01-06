<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}
