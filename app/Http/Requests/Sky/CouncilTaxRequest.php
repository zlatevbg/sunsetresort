<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class CouncilTaxRequest extends Request
{
    protected $rules = [
        'apartment_id' => 'required|exists:apartments,id',
        'tax' => 'required|numeric|digits_between:1,8',
        'checked_at' => 'required|date',
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
