<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class BookingRequest extends Request
{
    protected $rules = [
        'apartment_id' => 'required|numeric',
        'owner_id' => 'required|numeric|exists:owners,id',
        /*'kitchen_items' => 'numeric|in:0,1',
        'loyalty_card' => 'numeric|in:0,1,2',
        'club_card' => 'numeric|in:0,1',*/
        'exception' => 'numeric|in:0,1',
        /*'deposit_paid' => 'numeric|in:0,1',
        'hotel_card' => 'numeric|in:0,1',*/
        'arrive_at' => 'required|date',
        'arrival_airport_id' => 'numeric',
        'arrival_transfer' => 'in:car,minibus',
        'departure_at' => 'date',
        'departure_airport_id' => 'numeric',
        'departure_transfer' => 'in:car,minibus',
        'adults.*' => 'required',
        'adults.0' => 'required',
        'children.*' => 'required',
        'services' => 'filled|array',
        'accommodation_costs' => 'numeric|in:0,1',
        'transfer_costs' => 'numeric|in:0,1',
        'services_costs' => 'numeric|in:0,1',
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
        $this->merge(['services' => $this->input('services', null)]); // set default value of the services select

        return $this->rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        for ($i = 0, $count = count($this->input('adults')); $i < $count; $i++) {
            $messages['adults.' . $i . '.required'] = trans('validation.required', ['attribute' => trans('validation.attributes.adultField') . ' ' . ($i + 1)]);
        }
        for ($i = 0, $count = count($this->input('children')); $i < $count; $i++) {
            $messages['children.' . $i . '.required'] = trans('validation.required', ['attribute' => trans('validation.attributes.childField') . ' ' . ($i + 1)]);
        }
        return $messages;
    }
}
