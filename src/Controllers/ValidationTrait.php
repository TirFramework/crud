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
     * @param $validation
     * @return array
     * @throws ValidationException
     */
    public function storeValidation(Request $request, $validation): array
    {
        $validation = $this->replaceItemId($validation);
        return Validator::make($request->all(), $validation)->validate();
    }

    private function replaceItemId($validation, $item = null)
    {
        $itemId = isset($item) ? $item->getKey : '';
        array_walk_recursive($validation, function (&$validation) use ($itemId) {
            $validation = str_replace('{{itemId}}', $itemId, $validation);
        });
        return $validation;
    }

    public function updateValidation(request $request, $validation, $item)
    {

        $validation = $this->replaceItemId($validation, $item);
        return Validator::make($request->all(), $validation)->validate();

    }
}

