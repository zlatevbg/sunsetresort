<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'year', 'corporate_tax',
    ];

    public function rentalCompanies()
    {
        return $this->belongsToMany(RentalCompany::class)->withTimestamps();
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }
}
