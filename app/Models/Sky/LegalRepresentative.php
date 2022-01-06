<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class LegalRepresentative extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['access'];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'comments',
    ];

    public function access()
    {
        return $this->hasMany(LegalRepresentativeAccess::class);
    }
}
