<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class SignatureFiles extends Model
{
    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }
}
