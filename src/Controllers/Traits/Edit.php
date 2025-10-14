<?php

namespace Tir\Crud\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Support\Hooks\EditHooks;

trait Edit
{
    use EditHooks;

    public function edit(int|string $id)
    {
        // Access check is now handled automatically in callAction()

        // Define the default behavior as a closure
        $defaultEdit = function ($modelId = null) use ($id) {
            if ($modelId !== null) {
                $id = $modelId;
            }
            return $this->model()->findOrFail($id);
        };

        // Pass the closure to the hook
        $dataModel = $this->executeWithHook('onEdit', $defaultEdit, $id);

        // Handle response with hooks
        return $this->editResponse($dataModel);
    }

    private function editResponse($dataModel): mixed
    {
        // Define the default response behavior as a closure
        $defaultResponse = function ($model = null) use ($dataModel) {
            if ($model !== null) {
                $dataModel = $model;
            }
            $scaffold = $this->scaffolder()->scaffold('edit', $dataModel)->getEditScaffold();

            // Override actions with access-filtered ones
            $scaffold['configs']['actions'] = $this->getAvailableActions();

            return Response::json($scaffold, 200);
        };

        // Pass the closure to the response hook
        return $this->executeWithHook('onEditResponse', $defaultResponse, $dataModel);
    }
}
