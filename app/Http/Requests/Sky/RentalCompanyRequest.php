<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\RentalCompany;

class RentalCompanyRequest extends Request
{
    protected $rules = [
        'address' => 'required|max:255',
        'bulstat' => 'required|digits:9',
        'egn' => 'required|digits:10',
        'id_card' => 'required|digits:9',
        'manager' => 'required|max:255',
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
            $company = RentalCompany::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:rental_company_translations,name,' . $company->id . ',rental_company_id,locale,' . \Locales::getCurrent());
        } else {
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:rental_company_translations,name,NULL,rental_company_id,locale,' . \Locales::getCurrent());
        }

        return $this->rules;
    }
}
