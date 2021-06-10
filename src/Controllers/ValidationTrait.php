<?php


namespace Tir\Crud\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait ValidationTrait
{
    /**
     * @param Request $request
     * @param $item
     */


    /**
     * Run validator on request
     * @param Request $request
     * @param array $validationRules
     * @return array
     * @throws ValidationException
     */
    public function storeValidation(Request $request, array $validationRules): array
    {
        return Validator::make($request->all(), $validationRules)->validate();
    }


    public function updateValidation(request $request, array $validationRules): array
    {
        return Validator::make($request->all(), $validationRules)->validate();

    }
}

