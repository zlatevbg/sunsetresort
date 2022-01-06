<?php

namespace App\Models\Owners;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BuildingMm extends Model
{
    protected $table = 'building_mm';

    protected $dates = [
        'deadline_at',
    ];

    public function documents()
    {
        return $this->hasMany(BuildingMmDocuments::class)->whereIn('type', ['rules', 'insurance', 'receipts', 'communal-fee-en', 'communal-fee-ru', 'court-decision']);
    }

    public function financials()
    {
        return $this->hasMany(BuildingMmDocuments::class)->whereIn('type', ['accounts', 'ier', 'budget', 'electricity', 'water', 'eur-account', 'bgn-account', 'audit-report-condominium', 'audit-conclusion-condominium', 'audit-report-management', 'audit-conclusion-management']);
    }

    public function company()
    {
        return $this->hasOne(ManagementCompany::class, 'id', 'management_company_id');
    }

    public function getDeadlineAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d.m.Y') : null;
    }
}
