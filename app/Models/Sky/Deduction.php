<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Deduction extends Model
{
    use SoftDeletes, Translatable;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'is_taxable',
    ];

    public $translatedAttributes = [
        'name'
    ];

    // protected $with = ['translations'];

    public function contractDeductions()
    {
        return $this->belongsTo(ContractDeduction::class);
    }
}
