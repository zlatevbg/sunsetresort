<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class PoolUsageContractTrackerRequest extends Request
{
    protected $rules = [
        'apartment_id' => 'required|numeric',
        'owner_id' => 'required|numeric',
        'year_id' => 'required|numeric',
    ];

    /**
     * Determine if the client is authorized to make this request.
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
