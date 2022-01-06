<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class Request extends FormRequest
{
    /**
     * Sanitizing Form Data Before Validating
     */
    public function all()
    {
        $attributes = parent::all();

        if (isset($attributes['slug'])) {
            $attributes['slug'] = str_slug($attributes['slug']);
        }

        $this->replace($attributes);

        return parent::all();

    }
}
