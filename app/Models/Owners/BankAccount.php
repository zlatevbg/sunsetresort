<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    public function ownership()
    {
        return $this->hasMany(Ownership::class);
    }
}
