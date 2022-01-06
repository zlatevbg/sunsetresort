<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class SignatureFiles extends Model
{
    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'order', 'signature_id',
    ];

    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }
}
