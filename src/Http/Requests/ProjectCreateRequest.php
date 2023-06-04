<?php

namespace Bnoordsij\ClassTrees\Http\Requests;

class ProjectCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'path' => ['required', 'string', 'max:250'],
            'starting_file' => ['required', 'string', 'max:250'],
        ];
    }
}
