<?php

namespace Tir\Crud\Support\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;

class CrudRequest extends FormRequest
{
    private array $original;
    private array $unDoted;

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


    private function getRules()
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

    protected function prepareForValidation()
    {
        $this->unDoted = Arr::undot($this->all());
        $this->original = $this->all();

        foreach ($this->original as $offset => $value){
            $this->offsetUnset($offset);
        }
        $this->merge($this->unDoted);
    }


    protected function passedValidation(){

        if($this->input('crudModel')->getConnection()->getDriverName() === 'mongodb'){

            foreach ($this->unDoted as $offset=> $value) {
                $this->offsetUnset($offset);
            }
            $this->merge($this->groupByNumber($this->original));
        }

        $this->offsetUnset('crudModel');
        $this->offsetUnset('crudModuleName');
        $this->offsetUnset('crudActionName');
    }

    private function groupByNumber($array): array
    {
        $result = array();

        foreach ($array as $key => $value) {
            $parts = preg_split('/\.\d+\./', $key);
            if(count($parts) == 1){
                $result[$key] = $value;
            }else{
                preg_match('/\.\d+\./',$key,$matches);
                $index = str_replace('.','',$matches)[0] ?? null;
                $prefix = $parts[0] ?? null;
                $suffix = $parts[1] ?? null;

                if($suffix){
                    $result[$prefix][$index][$suffix] = $value;
                }else{
                    $result[$prefix][$index] = $value;
                }
            }

        }

        return $result;
    }



}
