<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class RentalContractRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'mm_covered' => 'required|in:0,50,100',
        'deadline_at' => 'required|date',
        'min_duration' => 'required|numeric|min:1|max:100',
        'max_duration' => 'required|numeric|min:1|max:100',
        'contract_dfrom1' => 'required|date',
        'contract_dto1' => 'required_with:contract_dfrom1|date',
        'contract_dfrom2' => 'date',
        'contract_dto2' => 'required_with:contract_dfrom2|date',
        'personal_dfrom1' => 'date',
        'personal_dto1' => 'required_with:personal_dfrom1|date',
        'personal_dfrom2' => 'date',
        'personal_dto2' => 'required_with:personal_dfrom2|date',
        'rental_payment_id' => 'present|numeric',
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
