<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $dates = ['deleted_at'];

    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

}
