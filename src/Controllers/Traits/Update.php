<?php

namespace Tir\Crud\Controllers\Traits;

use Illuminate\Http\Request;
use Tir\Crud\Services\UpdateService;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Support\Hooks\UpdateHooks;
use Tir\Crud\Support\Hooks\RequestHooks;

trait Update
{
    use ProcessRequest;
    use UpdateHooks;
    use RequestHooks;



    public function update(Request $request, int|string $id)
    {

        // First process the request data
        $processedRequest = $this->processRequest($request);

        // Then validate the request
        $this->validateUpdateRequest($processedRequest, $id);

        $item = $this->updateCrud($processedRequest, $id);

        return $this->updateResponse($item);


    }

    public function inlineUpdate(Request $request, int|string $id)
    {
        // First process the request data
        $processedRequest = $this->processRequest($request);

        // Then validate the request
        $this->validateInlineUpdateRequest($processedRequest, $id);

        // Update the item
        $item = $this->updateCrud($processedRequest, $id);

        // Return the response
        return $this->updateResponse($item);
    }

    private function updateCrud($request, $id)
    {
        $defaultUpdate = function ($req = null, $modelId = null) use ($request, $id) {
            if ($req !== null) {
                $request = $req;
            }
            if ($modelId !== null) {
                $id = $modelId;
            }


            $updateService = new UpdateService($this->scaffolder());

            // Pass hooks to service
            if (isset($this->crudHookCallbacks)) {
                $updateService->setHooks($this->crudHookCallbacks);
            }

            return $updateService->update($request, $id);

        };

        return $this->executeWithHook('onUpdate', $defaultUpdate, $request, $id);

    }


    private function updateResponse($item): mixed
    {
        // Define the default response behavior as a closure
        $defaultResponse = function ($i = null) use ($item) {
            if ($i !== null) {
                $item = $i;
            }

            $moduleName = $this->scaffolder()->getModuleName();
            $message = trans('core::message.item-updated', ['item' => trans("message.item.$moduleName")]);
            $scaffolder = $this->scaffolder()->scaffold('edit', $item)->getEditScaffold();
            return Response::Json(
                [
                    'id' => $item->id,
                    'changes' => $item->getChanges(),
                    'scaffolder' => $scaffolder,
                    'updated' => true,
                    'message' => $message,
                ]
                ,
                200
            );
        };

        // Pass the closure to the response hook
        return $this->executeWithHook('onUpdateResponse', $defaultResponse, $item);
    }
}
