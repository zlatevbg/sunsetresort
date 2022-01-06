<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class OwnerFiles extends Model
{
    protected $fillable = [
        'file', 'uuid', 'extension', 'size', 'name', 'owner_id',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }
}
