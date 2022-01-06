<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class KeyLogRequest extends Request
{
    protected $rules = [
        'occupied_at' => 'required|date',
        'apartment_id' => 'required|numeric',
        'people' => 'required|max:255',
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
