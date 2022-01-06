<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Deduction;

class DeductionRequest extends Request
{
    protected $rules = [
        'is_taxable' => 'filled|boolean'
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
        $this->merge(['is_taxable' => $this->input('is_taxable', 0)]); // set default value of the is_taxable checkbox

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $deduction = Deduction::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:deduction_translations,name,' . $deduction->id . ',deduction_id,locale,' . \Locales::getCurrent());
        } else {
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:deduction_translations,name,NULL,deduction_id,locale,' . \Locales::getCurrent());
        }

        return $this->rules;
    }
}
