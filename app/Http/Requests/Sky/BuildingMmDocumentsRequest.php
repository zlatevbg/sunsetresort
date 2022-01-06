<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class BuildingMmDocumentsRequest extends Request
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
        $this->rules = array_add($this->rules, 'type', 'present|in:' . implode(',', array_keys(trans(\Locales::getNamespace() . '/multiselect.buildingMMDocuments'))));

        return $this->rules;
    }
}
