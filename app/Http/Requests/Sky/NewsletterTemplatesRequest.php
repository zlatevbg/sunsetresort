<?php

namespace App\Http\Requests\Sky;

use App\Http\Requests\Request;

class NewsletterTemplatesRequest extends Request
{
    protected $rules = [
        'template' => 'required|alpha_dash|max:255',
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
        'recipients' => 'required|array|in:all,subscribed,unsubscribed,srioc,letting,mm,bills',
        'signature_id' => 'required|numeric|exists:signatures,id',
        'subject' => 'required|max:255',
        'teaser' => 'required|max:255',
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
        $this->merge(['recipients' => $this->input('recipients', null)]); // set default value of the recipients select

        return $this->rules;
    }
}
