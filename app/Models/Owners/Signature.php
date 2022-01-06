<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Signature extends Model
{
    use Translatable;

    public $translatedAttributes = [
        'name', 'description', 'content',
    ];

    // protected $with = ['translations'];

    public function images()
    {
        return $this->hasMany(SignatureFiles::class)->orderBy('order');
    }
}
