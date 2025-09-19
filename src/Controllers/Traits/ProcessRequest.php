<?php

namespace Tir\Crud\Controllers\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Tir\Crud\Support\Hooks\RequestHooks;
use Tir\Crud\Support\Database\DatabaseAdapterFactory;

trait ProcessRequest
{
    use RequestHooks;
    /**
     * Process the request data using database adapters
     */
    private function processRequest(Request $request)
    {
        // Define the default behavior as a closure
        $defaultProcessRequest = function($req = null) use ($request) {
            if ($req !== null) {
                $request = $req;
            }

            // Use database adapter for request processing
            $adapter = DatabaseAdapterFactory::create($this->model->getConnection());
            $scaffolderFields = $this->scaffolder()->scaffold('edit')->fieldsHandler()->getAllDataFields();

            // Let adapter handle field filtering and format conversion
            $processedData = $adapter->processRequestData($request->all(), $scaffolderFields);

            // Replace request with processed data
            $request->replace([]);
            $request->merge($processedData);

            return $request;
        };

        // Pass the closure to the hook
        $customProcessRequest = $this->callHook('onProcessRequest', $defaultProcessRequest, $request);
        if($customProcessRequest !== null) {
            return $customProcessRequest;
        }

        // Otherwise, return the result directly
        return $defaultProcessRequest();
    }

    /**
     * Validate the create request
     */
    private function validateCreateRequest(Request $request)
    {
        // Define the default behavior as a closure
        $defaultStoreValidation = function($req = null) use ($request) {
            if ($req !== null) {
                $request = $req;
            }

            $rules = $this->scaffolder()->getCreationRules();
            $validator = Validator::make($request->all(), $rules);
            $validator->validate();

            return true;
        };

        // Pass the closure to the hook
        $customStoreValidation = $this->callHook('onStoreValidation', $defaultStoreValidation, $request);
        if($customStoreValidation !== null) {
            return $customStoreValidation;
        }

        // Otherwise, return the result directly
        return $defaultStoreValidation();
    }

    /**
     * Validate the update request
     */
    private function validateUpdateRequest(Request $request, $id)
    {
        // Define the default behavior as a closure
        $defaultUpdateValidation = function($req = null, $modelId = null) use ($request, $id) {
            if ($req !== null) {
                $request = $req;
            }
            if ($modelId !== null) {
                $id = $modelId;
            }

            $rules = $this->scaffolder()->getUpdateRules();
            $validator = Validator::make($request->all(), $rules);
            $validator->validate();

            $request = $this->passedValidation($request);

            return true;
        };

        // Pass the closure to the hook
        $customUpdateValidation = $this->callHook('onUpdateValidation', $defaultUpdateValidation, $request, $id);
        if($customUpdateValidation !== null) {
            return $customUpdateValidation;
        }

        // Otherwise, return the result directly
        return $defaultUpdateValidation();
    }


    private function validateInlineUpdateRequest(Request $request, $id, $options = [])
    {
        // Define the default behavior as a closure
        $defaultInlineUpdateValidation = function($req = null, $modelId = null) use ($request, $id) {
            if ($req !== null) {
                $request = $req;
            }
            if ($modelId !== null) {
                $id = $modelId;
            }

            $rules = $this->scaffolder()->getInlineUpdateRules();
            $validator = Validator::make($request->all(), $rules);
            $validator->validate();

            return true;
        };

        // Pass the closure to the hook
        $customInlineUpdateValidation = $this->callHook('onInlineUpdateValidation', $defaultInlineUpdateValidation, $request, $id);
        if($customInlineUpdateValidation !== null) {
            return $customInlineUpdateValidation;
        }

        // Otherwise, return the result directly
        return $defaultInlineUpdateValidation();
    }

    private function passedValidation($request)
    {
        // ProcessRequest now handles all request processing
        // No additional processing needed here
        return $request;
    }
}
