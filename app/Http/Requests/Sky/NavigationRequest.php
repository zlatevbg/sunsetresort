<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Navigation;

class NavigationRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'type' => 'alpha_dash|max:255',
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
    public function rules(Navigation $page)
    {
        $this->merge(['is_category' => $this->input('is_category', 0)]); // set default value of the is_category checkbox
        $this->merge(['is_popup' => $this->input('is_popup', 0)]); // set default value of the is_popup checkbox

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $page = Navigation::findOrFail(\Request::input('id'))->first();
            $this->rules = array_add($this->rules, 'slug', 'present|max:255|unique:navigation,slug,' . $page->id . ',id,parent,' . $page->parent . ',locale_id,' . $page->locale_id);
        } else {
            $this->rules = array_add($this->rules, 'slug', 'present|max:255|unique:navigation,slug,NULL,id,parent,' . \Request::input('parent') . ',locale_id,' . \Request::input('locale'));
        }

        return $this->rules;
    }
}
