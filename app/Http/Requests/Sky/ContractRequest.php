<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class ContractRequest extends Request
{
    protected $rules = [
        'is_exception' => 'required|numeric|in:0,1',
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
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $this->rules = [
                'signed_at' => 'required|date',
            ];
        } else {
            $this->rules = [
                'rental_contract_id' => 'required|numeric',
                'mm_for_year' => 'required|size:4',
                'duration' => 'required|numeric|min:1|max:100',
                'signed_at' => 'required|date',
            ];
        }

        return $this->rules;
    }
}
