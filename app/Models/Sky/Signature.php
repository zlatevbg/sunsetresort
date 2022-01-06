<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;

class Signature extends Model
{
    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'description', 'content',
    ];

    public $translatedAttributes = [
        'name', 'content',
    ];

    // protected $with = ['translations'];

    public function images()
    {
        return $this->hasMany(SignatureFiles::class)->orderBy('order');
    }
}
