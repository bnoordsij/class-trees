<?php

namespace App\Http\Requests;

class ProjectUpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'namespace_rules' => ['nullable', 'string'],
        ];
    }
}
