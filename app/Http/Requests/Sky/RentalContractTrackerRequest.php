<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class RentalContractTrackerRequest extends Request
{
    protected $rules = [
        'apartment_id' => 'required|numeric',
        'owner_id' => 'required|numeric',
        'rental_contract_id' => 'required|numeric',
        'proxy_id' => 'numeric',
        'from' => 'digits:4|poa',
        'to' => 'digits:4',
        'price' => 'present|numeric|digits_between:1,10',
        'price_tc' => 'numeric|digits_between:1,10',
        'mm_for_year' => 'sometimes|required|size:4',
        'mm_for_years' => 'sometimes|required',
        'duration' => 'required|numeric|min:1|max:100',
        'is_exception' => 'required|numeric|in:0,1',
        'contract_dfrom1' => 'required|date',
        'contract_dto1' => 'required_with:contract_dfrom1|date',
        'contract_dfrom2' => 'date',
        'contract_dto2' => 'required_with:contract_dfrom2|date',
        'personal_dfrom1' => 'date',
        'personal_dto1' => 'required_with:personal_dfrom1|date',
        'personal_dfrom2' => 'date',
        'personal_dto2' => 'required_with:personal_dfrom2|date',
    ];

    /**
     * Determine if the client is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return $this->rules;
    }
}
