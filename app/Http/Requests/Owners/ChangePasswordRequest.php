<?php

namespace App\Http\Requests\Owners;

use App\Http\Requests\Request;

class ChangePasswordRequest extends Request
{
    protected $rules = [
        'password' => 'required|confirmed|min:6',
    ];

    /**
     * Determine if the admin is authorized to make this request.
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
