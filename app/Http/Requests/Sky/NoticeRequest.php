<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class NoticeRequest extends Request
{
    protected $rules = [
        'name' => 'required|max:255',
        'content' => 'required',
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
        $this->merge(['auto_assign' => $this->input('auto_assign', 0)]); // set default value of the auto_assign checkbox

        return $this->rules;
    }
}
