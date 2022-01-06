<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Year;

class YearRequest extends Request
{
    protected $rules = [
        'year' => 'required|digits:4|unique:years',
        'corporate_tax' => 'required|digits_between:1,3',
        'companies' => 'required|array',
        'fees' => 'required|array',
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
            $year = Year::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'year');
            $this->rules = array_add($this->rules, 'year', 'required|max:255|unique:years,year,' . $year->id);
        }

        return $this->rules;
    }
}
