<?php

namespace App\Models\Sky;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Askedio\SoftCascade\Traits\SoftCascadeTrait;

class Apartment extends Model
{
    use SoftDeletes, SoftCascadeTrait;

    protected $softCascade = ['ownership', 'contracts', 'agents', 'keyholders', 'legalRepresentatives'];

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'apartment_area',
        'balcony_area',
        'extra_balcony_area',
        'common_area',
        'total_area',
        'comments',
        'room_id',
        'furniture_id',
        'view_id',
        'project_id',
        'building_id',
        'floor_id',
        'mm_tax_formula',
    ];

    /*protected static function boot() {
        parent::boot();

        static::deleting(function($apartment) {
            \DB::table('agent_access')->where('apartment_id', $apartment->id)->delete();
            \DB::table('legal_representative_access')->where('apartment_id', $apartment->id)->delete();
            \DB::table('contracts')->where('apartment_id', $apartment->id)->delete();
            \DB::table('keyholder_access')->where('apartment_id', $apartment->id)->delete();
            \DB::table('newsletter_archive')->where('apartment_id', $apartment->id)->delete();
            \DB::table('contract_payments')->where('apartment_id', $apartment->id)->delete();
            \DB::table('mm_fees_payments')->where('apartment_id', $apartment->id)->delete();
            \DB::table('communal_fees_payments')->where('apartment_id', $apartment->id)->delete();
            \DB::table('pool_usage_payments')->where('apartment_id', $apartment->id)->delete();
        });
    }*/

    public function ownership()
    {
        return $this->hasMany(Ownership::class)->orderBy('owner_id');
    }

    public function owners()
    {
        return $this->hasMany(Ownership::class)->with('owner');
    }

    public function allowners()
    {
        return $this->hasMany(Ownership::class)->with(['owner' => function ($query) {
            $query->withTrashed();
        }]);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class); // do not set ->whereNull('contracts.deleted_at') as this destroyes relantionships, e.g. in ReportRentalPayments.php : contracts->withTrashed()
    }

    public function agents()
    {
        return $this->hasMany(LegalRepresentativeAccess::class);
    }

    public function legalRepresentatives()
    {
        return $this->hasMany(LegalRepresentativeAccess::class);
    }

    public function keyholders()
    {
        return $this->hasMany(KeyholderAccess::class);
    }

    public function mmFeesPayments()
    {
        return $this->hasMany(MmFeePayment::class);
    }

    public function communalFeesPayments()
    {
        return $this->hasMany(CommunalFeePayment::class);
    }

    public function poolUsagePayments()
    {
        return $this->hasMany(PoolUsagePayment::class);
    }

    public function poolUsageContracts()
    {
        return $this->hasMany(PoolUsageContractTracker::class);
    }

    public function buildingMM()
    {
        return $this->hasMany(BuildingMm::class, 'building_id', 'building_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function rooms()
    {
        // for calculateMmFees && calculateRentalOptions
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function furniture()
    {
        return $this->belongsTo(Furniture::class);
    }

    public function view()
    {
        return $this->belongsTo(View::class);
    }

    public function newsletters()
    {
        return $this->belongsToMany(Newsletter::class)->withTimestamps();
    }

    public function setApartmentAreaAttribute($value)
    {
        $this->attributes['apartment_area'] = $value ?: null;
    }

    public function setBalconyAreaAttribute($value)
    {
        $this->attributes['balcony_area'] = $value ?: null;
    }

    public function setExtraBalconyAreaAttribute($value)
    {
        $this->attributes['extra_balcony_area'] = $value ?: null;
    }

    public function setCommonAreaAttribute($value)
    {
        $this->attributes['common_area'] = $value ?: null;
    }

    public function setTotalAreaAttribute($value)
    {
        $this->attributes['total_area'] = $value ?: null;
    }
}
