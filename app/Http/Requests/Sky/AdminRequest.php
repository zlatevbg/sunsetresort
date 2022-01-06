<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Admin;

class AdminRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email|max:255|unique:admins',
        'phone' => ['present', 'max:255', 'regex:/^[0-9\+\s]+$/'],
        'password' => 'required|confirmed|min:6', // ['regex:/^(?=.*\p{Ll})(?=.*\p{Lu})(?=.*[\p{N}\p{P}]).{6,}$/u'], // /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d\W]).{6,}$/ // http://www.zorched.net/2009/05/08/password-strength-validation-with-regular-expressions/
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
        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $admin = Admin::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'password');
            $this->rules = array_add($this->rules, 'password', 'present|confirmed|min:6');

            array_forget($this->rules, 'email');
            $this->rules = array_add($this->rules, 'email', 'required|email|max:255|unique:admins,email,' . $admin->id);
        }

        return $this->rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'password.regex' => trans('passwords.regex'),
        ];
    }
}
