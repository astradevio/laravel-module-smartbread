<?php

namespace Modules\{Module}\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {Model}Request extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [ 'required', 'string' ]
        ];
    }
}
