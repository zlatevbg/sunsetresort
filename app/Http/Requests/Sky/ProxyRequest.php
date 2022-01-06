<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Proxy;

class ProxyRequest extends Request
{
    protected $rules = [
        'is_company' => 'filled|in:0,1',
        'name' => 'required|max:255',
        'bulstat' => 'filled|digits:9|unique:proxies',
        'egn' => 'filled|digits:10|unique:proxies',
        'id_card' => 'filled|digits:9',
        'issued_at' => 'filled|date',
        'address' => 'required|max:255',
        'issued_by' => 'filled|max:255',
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
        $this->merge(['is_company' => $this->input('is_company', 0)]); // set default value of the is_company checkbox

        if (in_array($this->method(), ['PUT', 'PATCH'])) {
            $proxy = Proxy::findOrFail(\Request::input('id'))->first();
            array_forget($this->rules, 'bulstat');
            array_forget($this->rules, 'egn');
            $this->rules = array_add($this->rules, 'bulstat', 'filled|digits:9|unique:proxies,bulstat,' . $proxy->id);
            $this->rules = array_add($this->rules, 'egn', 'filled|digits:10|unique:proxies,egn,' . $proxy->id);
        }
        return $this->rules;
    }
}
