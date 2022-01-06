<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'year_id',
        'room_id',
        'annual_communal_tax',
        'daily_communal_tax',
        'pool_tax',
        'pool_bracelets',
        'aquapark_tax',
        'pool_aquapark_tax',
    ];

    public $timestamps = false;

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

}
