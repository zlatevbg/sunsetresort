<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rental', 'bank_iban', 'bank_bic', 'bank_beneficiary', 'bank_name', 'owner_id', 'comments',
    ];

    public function ownership()
    {
        return $this->hasMany(Ownership::class);
    }
}
