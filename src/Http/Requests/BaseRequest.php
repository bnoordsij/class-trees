<?php

namespace Bnoordsij\ClassTrees\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    public function authorize() // we use policies for that
    {
        return true;
    }
}
