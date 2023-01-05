<?php

namespace Tir\Crud\Support\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CrudRequest extends FormRequest
{
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return $this->getRules();
    }


    public function getRules()
    {

        if ($this->method() == 'POST') {
            return $this->input('crudModel')->getCreationRules();
        }

        if ($this->method() == 'PUT' || $this->method() == 'PATCH') {
            return $this->input('crudModel')->getUpdateRules();
        }
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'error' => 'validation_error',
            'message' => $validator->errors()
        ], 422
        ));
    }


}
