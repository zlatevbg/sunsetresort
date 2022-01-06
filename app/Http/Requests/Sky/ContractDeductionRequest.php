<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class ContractDeductionRequest extends Request
{
    protected $rules = [
        'deduction_id' => 'required|numeric',
        'amount' => 'required|numeric|digits_between:1,8',
        'signed_at' => 'present|date',
    ];

    /**
     * Determine if the user is authorized to make this request.
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
