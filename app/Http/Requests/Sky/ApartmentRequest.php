<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;
use App\Models\Sky\Apartment;

class ApartmentRequest extends Request
{
    protected $rules = [
        'number' => 'required|max:255|unique:apartments',
        'apartment_area' => 'present|numeric',
        'balcony_area' => 'present|numeric',
        'extra_balcony_area' => 'present|numeric',
        'common_area' => 'present|numeric',
        'total_area' => 'present|numeric',
        'room_id' => 'required|exists:rooms,id',
        'furniture_id' => 'required|exists:furniture,id',
        'view_id' => 'required|exists:views,id',
        'project_id' => 'required|exists:projects,id',
        'building_id' => 'required|exists:buildings,id',
        'floor_id' => 'required|exists:floors,id',
        'mm_tax_formula' => 'required|numeric',
    ];

    public function __construct(\Illuminate\Http\Request $request)
    {
        if (!$request->has('project_id')) {
            $request->merge(['project_id' => $request->input('project')]);
        }

        if (!$request->has('building_id')) {
            $request->merge(['building_id' => $request->input('building')]);
        }

        if (!$request->has('floor_id')) {
            $request->merge(['floor_id' => $request->input('floor')]);
        }
    }

    /**
     * Determine if the apartment is authorized to make this request.
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
            $apartment = Apartment::findOrFail(\Request::input('id'))->first();

            array_forget($this->rules, 'number');
            $this->rules = array_add($this->rules, 'number', 'required|max:255|unique:apartments,number,' . $apartment->id);
        }

        return $this->rules;
    }

}
