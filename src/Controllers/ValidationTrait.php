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
        $validationRules = $this->replaceItemId($validationRules);
        return Validator::make($request->all(), $validationRules)->validate();
    }

    private function replaceItemId(array $validationRules, $item = null)
    {
        $itemId = isset($item) ? $item->getKey : '';
        array_walk_recursive($validationRules, function (&$validationRules) use ($itemId) {
            $validationRules = str_replace('{{itemId}}', $itemId, $validationRules);
        });
        return $validationRules;
    }

    public function updateValidation(request $request, array $validationRules, $item)
    {
        $validationRules = $this->replaceItemId($validationRules, $item);
        return Validator::make($request->all(), $validationRules)->validate();

    }
}

