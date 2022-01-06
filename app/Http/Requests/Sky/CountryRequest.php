<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Country;

class CountryRequest extends Request
{
    protected $rules = [];

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
            $country = Country::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:country_translations,name,' . $country->id . ',country_id,locale,' . \Locales::getCurrent());
        } else {
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:country_translations,name,NULL,country_id,locale,' . \Locales::getCurrent());
        }

        return $this->rules;
    }
}
