<?php

namespace App\Models\Sky;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Owner extends Authenticatable
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['ownership'];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'mobile',
        'email',
        'email_cc',
        'password',
        'temp_password',
        'sex',
        'city',
        'postcode',
        'address1',
        'address2',
        'comments',
        'locale_id',
        'country_id',
        'apply_wt',
        'outstanding_bills',
        'letting_offer',
        'srioc',
        'is_subscribed',
        'bulstat',
        'tax_pin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*protected static function boot() {
        parent::boot();

        static::deleting(function($owner) {
            \DB::table('notice_owner')->where('owner_id', $owner->id)->delete();
            \DB::table('newsletter_archive')->where('owner_id', $owner->id)->delete();
            \DB::table('bank_accounts')->where('owner_id', $owner->id)->delete();
            \DB::table('council_tax')->where('owner_id', $owner->id)->delete();
            \DB::table('contract_payments')->where('owner_id', $owner->id)->delete();
            \DB::table('mm_fees_payments')->where('owner_id', $owner->id)->delete();
        });
    }*/

    public function notices()
    {
        return $this->belongsToMany(Notice::class)->withTimestamps();
    }

    public function ownership()
    {
        return $this->hasMany(Ownership::class);
    }

    public function apartments()
    {
        return $this->hasMany(Ownership::class)->with('apartment');
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class)->withTimestamps();
    }

    public function tax()
    {
        return $this->belongsTo(CouncilTax::class)->withTimestamps();
    }

    public function contractPayments()
    {
        return $this->belongsTo(ContractPayment::class);
    }

    public function mmFeesPayments()
    {
        return $this->belongsTo(MmFeePayment::class);
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ?: null;
    }

    public function setEmailCcAttribute($value)
    {
        $this->attributes['email_cc'] = $value ?: null;
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getFullAddressAttribute()
    {   $address = [];
        array_push($address, $this->address1, $this->address2, $this->postcode, $this->city, $this->country->translate($this->locale->locale)->name);
        return implode(', ', array_filter($address));
    }
}
