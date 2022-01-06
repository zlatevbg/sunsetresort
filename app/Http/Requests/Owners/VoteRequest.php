<?php

namespace App\Http\Requests\Owners;

use App\Http\Requests\Request;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\MessageBag;

class VoteRequest extends Request
{
    protected $rules = [
        'q1' => 'required_without:q2',
        'q2' => 'required_without:q1',
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

    protected function formatErrors(Validator $validator)
    {
        $errors = $validator->errors()->messages();

        if (array_key_exists('q1', $errors) && array_key_exists('q2', $errors)) {
            $errors['q1'] = [];
            $errors['q2'] = [];
            array_push($errors, [trans(\Locales::getNamespace() . '/forms.answerAtLeastOne')]);
        }

        return $errors;
    }
}
