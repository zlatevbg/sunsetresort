<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Domain;

class DomainRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'domain' => 'required|max:255|unique:domains',
        'route' => 'required|max:255',
        'locales' => 'required|array',
        'default_locale_id' => 'required|numeric',
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
        $this->merge(['hide_default_locale' => $this->input('hide_default_locale', 0)]); // set default value of the hide_default_locale checkbox

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $domain = Domain::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'domain');
            $this->rules = array_add($this->rules, 'domain', 'required|max:255|unique:domains,domain,' . $domain->id);
        }

        return $this->rules;
    }
}
