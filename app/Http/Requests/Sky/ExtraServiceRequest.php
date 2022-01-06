<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\ExtraService;

class ExtraServiceRequest extends Request
{
    protected $rules = [
        'price' => 'filled|numeric|digits_between:1,6',
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
            $extraservice = ExtraService::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:extra_service_translations,name,' . $extraservice->id . ',extra_service_id,locale,' . \Locales::getCurrent());
        } else {
            $this->rules = array_add($this->rules, 'name', 'required|max:255|unique:extra_service_translations,name,NULL,extra_service_id,locale,' . \Locales::getCurrent());
        }

        return $this->rules;
    }
}
