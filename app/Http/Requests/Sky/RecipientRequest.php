<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Recipient;

class RecipientRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'email' => 'required|email|max:255|unique:recipients',
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
            $recipient = Recipient::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'email');
            $this->rules = array_add($this->rules, 'email', 'required|max:255|unique:recipients,email,' . $recipient->id);
        }

        return $this->rules;
    }
}
