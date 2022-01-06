<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class BankAccountRequest extends Request
{
    protected $rules = [
        'apartments' => 'required|array',
        'rental' => 'required|in:0,34,50,66,100',
        'bank_iban' => 'required|max:255',
        'bank_bic' => 'required|max:255',
        'bank_beneficiary' => 'required|max:255',
        'bank_name' => 'required|max:255',
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
