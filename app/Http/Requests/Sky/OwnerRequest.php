<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Owner;

class OwnerRequest extends Request
{
    protected $rules = [
        'first_name' => 'required|max:255',
        'last_name' => 'present|max:255',
        'sex' => 'required|in:female,male,not-applicable,not-known',
        'locale_id' => 'required|exists:locales,id',
        'phone' => ['present', 'max:255', 'regex:/^[0-9\+\s]+$/'],
        'mobile' => ['present', 'max:255', 'regex:/^[0-9\+\s]+$/'],
        'is_subscribed' => 'required|numeric|in:0,1',
        'email' => 'present|email|max:255|unique:owners',
        'email_cc' => 'present|email|max:255|unique:owners',
        'password' => 'required|confirmed|min:6', // ['regex:/^(?=.*\p{Ll})(?=.*\p{Lu})(?=.*[\p{N}\p{P}]).{6,}$/u'], // /^(?=.*[a-z])(?=.*[A-Z])(?=.*[\d\W]).{6,}$/ // http://www.zorched.net/2009/05/08/password-strength-validation-with-regular-expressions/
        'country_id' => 'required|exists:countries,id',
        'city' => 'present|max:255',
        'postcode' => 'present|max:255',
        'address1' => 'present|max:255',
        'address2' => 'present|max:255',
        'apply_wt' => 'required|numeric|in:0,1',
        'bulstat' => 'present|size:9',
        'tax_pin' => 'present|size:4',
        'outstanding_bills' => 'required|numeric|in:0,1',
        'letting_offer' => 'required|numeric|in:0,1',
        'srioc' => 'required|numeric|in:0,1',
    ];

    /**
     * Determine if the owner is authorized to make this request.
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
            $owner = Owner::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'password');
            $this->rules = array_add($this->rules, 'password', 'present|confirmed|min:6');

            array_forget($this->rules, 'email');
            array_forget($this->rules, 'email_cc');
            $this->rules = array_add($this->rules, 'email', 'present|email|max:255|unique:owners,email,' . $owner->id);
            $this->rules = array_add($this->rules, 'email_cc', 'present|email|max:255|unique:owners,email_cc,' . $owner->id);
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
