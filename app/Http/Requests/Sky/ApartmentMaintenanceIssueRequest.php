<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class ApartmentMaintenanceIssueRequest extends Request
{
    protected $rules = [
        'title' => 'required',
        'comments' => 'present',
        'status' => 'required|in:open,pending,completed',
        'responsibility' => 'required|in:owner,condominium,rental-company',
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
        return $this->rules;
    }
}
