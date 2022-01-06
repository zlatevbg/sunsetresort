<?php

namespace App\Services;

use App\Models\Sky\Poa;
use App\Models\Sky\RentalContractTracker;

class Validator
{
    public function validatePoa($attribute, $value, $parameters, $validator)
    {
        $poa = Poa::where('apartment_id', \Request::input('apartment_id'))->where('owner_id', \Request::input('owner_id'))->where('poa.is_active', 1)->where(function ($query) {
            $query->whereBetween('from', [\Request::input('from'), \Request::input('to')])->orWhereBetween('to', [\Request::input('from'), \Request::input('to')]);
        });

        if (\Request::has('id')) {
            if (\Request::has('rental_contract_id')) { // RentalContractTracker
                $rentalContract = RentalContractTracker::findOrFail(\Request::input('id'))->first();
                $poa = $poa->where('id', '!=', $rentalContract->poa_id);
            } else { // Poa
                $poa = $poa->where('id', '!=', \Request::input('id'));
            }
        }

        return !$poa->count();
    }
}
