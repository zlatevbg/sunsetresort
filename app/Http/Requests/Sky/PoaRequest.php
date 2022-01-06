<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class PoaRequest extends Request
{
    protected $rules = [
        'apartment_id' => 'required|numeric',
        'proxy_id' => 'required|numeric',
        'from' => 'required|digits:4|poa',
        'to' => 'required|digits:4',
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
