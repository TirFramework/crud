<?php

namespace Tir\Crud\Controllers\Traits;

use Illuminate\Support\Facades\Response;
use Tir\Crud\Support\Hooks\ShowHooks;

trait Show
{
    use ShowHooks;



    public function show($id)
    {

        // Define the default behavior as a closure
        $defaultShow = function ($modelId = null) use ($id) {
            if ($modelId !== null) {
                $id = $modelId;
            }
            return $this->model()->findOrFail($id);
        };

        // Pass the closure to the hook
        $dataModel = $this->executeWithHook('onShow', $defaultShow, $id);

        // Define the default response behavior as a closure
        $defaultResponse = function ($model = null) use ($dataModel) {
            if ($model !== null) {
                $dataModel = $model;
            }
            $scaffold = $this->scaffolder()->scaffold('detail', $dataModel)->getDetailScaffold();
            return Response::json($scaffold, 200);
        };

        // Pass the closure to the response hook
        return $this->executeWithHook('onShowResponse', $defaultResponse, $dataModel);
    }
}
