<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class ContractPaymentRequest extends Request
{
    protected $rules = [
        'payment_method_id' => 'required|numeric',
        'amount' => 'required|numeric|digits_between:1,8',
        'paid_at' => 'required|date',
        'rental_company_id' => 'required|numeric',
        'owner_id' => 'required|numeric',
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
