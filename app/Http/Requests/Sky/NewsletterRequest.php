<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class NewsletterRequest extends Request
{
    protected $rules = [
        'projects' => 'filled|array',
        'buildings' => 'filled|array',
        'floors' => 'filled|array',
        'rooms' => 'filled|array',
        'furniture' => 'filled|array',
        'views' => 'filled|array',
        'apartments' => 'filled|array',
        'owners' => 'filled|array',
        'countries' => 'filled|array',
        'locale_id' => 'required|numeric|exists:locales,id',
        'year_id' => 'required|numeric|exists:years,id',
        'merge' => 'filled|array',
        'merge.*' => 'required',
        'merge.0' => 'required_if:merge_by,1,2',
        'recipients' => 'required|array|in:all,subscribed,unsubscribed,srioc,letting,mm,bills',
        'merge_by' => 'filled|numeric|in:0,1,2',
        'signature_id' => 'required|numeric|exists:signatures,id',
        'subject' => 'required',
        'teaser' => 'required',
        'body' => 'required',
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
        $this->merge(['projects' => $this->input('projects', null)]); // set default value of the projects select
        $this->merge(['buildings' => $this->input('buildings', null)]); // set default value of the buildings select
        $this->merge(['floors' => $this->input('floors', null)]); // set default value of the floors select
        $this->merge(['rooms' => $this->input('rooms', null)]); // set default value of the rooms select
        $this->merge(['furniture' => $this->input('furniture', null)]); // set default value of the furniture select
        $this->merge(['views' => $this->input('views', null)]); // set default value of the views select
        $this->merge(['apartments' => $this->input('apartments', null)]); // set default value of the apartments select
        $this->merge(['owners' => $this->input('owners', null)]); // set default value of the owners select
        $this->merge(['countries' => $this->input('countries', null)]); // set default value of the countries select
        $this->merge(['recipients' => $this->input('recipients', null)]); // set default value of the recipients selectv

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
        for ($i = 0, $count = count($this->input('merge')); $i < $count; $i++) {
            $messages['merge.' . $i . '.required'] = trans('validation.required', ['attribute' => trans('validation.attributes.mergeField') . ' ' . ($i + 1)]);
        }
        return $messages;
    }
}
