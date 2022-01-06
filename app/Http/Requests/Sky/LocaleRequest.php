<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Locale;

class LocaleRequest extends Request
{
    protected $rules = [
        'locale' => 'required|unique:locales',
        'name' => 'required|max:255',
        'native' => 'required|max:255',
        'script' => 'required|size:3',
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
            $locale = Locale::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'locale');
            $this->rules = array_add($this->rules, 'locale', 'required|unique:locales,locale,' . $locale->id);
        }

        return $this->rules;
    }
}
