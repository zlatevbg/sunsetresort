<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class ApartmentAgentRequest extends Request
{
    protected $rules = [
        'agents' => 'required|array',
        'dfrom' => 'required|date',
        'dto' => 'required_with:dfrom|date',
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
