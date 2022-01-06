<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\ManagementCompany;

class ManagementCompanyRequest extends Request
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
            $company = ManagementCompany::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:management_company_translations,name,' . $company->id . ',management_company_id,locale,' . \Locales::getCurrent());
        } else {
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:management_company_translations,name,NULL,management_company_id,locale,' . \Locales::getCurrent());
        }

        return $this->rules;
    }
}
