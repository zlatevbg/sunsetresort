<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class CalendarRequest extends Request
{
    protected $rules = [
        'date' => 'required|date',
        'description' => 'required',
        'admins' => 'required|array',
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
