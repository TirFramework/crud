<?php

namespace Tir\Crud\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Tir\Crud\Services\DataService;
use Tir\Crud\Support\Hooks\TrashHooks;
use Tir\Crud\Support\Hooks\IndexDataHooks;

trait Trash
{
    use TrashHooks, IndexDataHooks;


    public function trash()
    {
        $cols = [];
        $scaffold = $this->scaffolder()->scaffold('index')->getIndexScaffold();

        $scaffold['configs']['actions'] = $this->getAvailableActions();
        foreach ($scaffold['fields'] as $index => $field) {
            $cols[$index] = [
                'title'      => $field->display,
                'dataIndex'  => $field->name,
                'fieldName'  => $field->name,
                'valueType'  => $field->valueType,
                'comment'    => $field->comment,
                'dataSet'    => $field->dataSet,
                'dataKey'    => $field->relation->key ?? null,
                'dataField'  => $field->relation->field ?? null,
                'relational' => isset($field->relation),
                'type'       => $field->type,
                'field'      => $field,
            ];
        }

        $data = [
            'configs'    => $scaffold['configs'],
            'cols'       => $cols,
            'dataRoute'  => route('admin.' . $this->scaffolder()->moduleName() . '.trashData'),
        ];

        return Response::json($data, 200);
    }

    public function trashData()
    {

        // Create DataService with trash mode
        $CrudService = new DataService($this->scaffolder(), $this->model());

        // Pass hooks from controller to service
        if (isset($this->crudHookCallbacks)) {
            $CrudService->setHooks($this->crudHookCallbacks);
        }

        // Get trash data
        $items = $CrudService->getData(true); // true for onlyTrashed

        // Handle response with hooks
        return $this->trashResponse($items);
    }

    private function trashResponse($items): mixed
    {
        // Define the default response behavior as a closure
        $defaultResponse = function ($i = null) use ($items) {
            if ($i !== null) {
                $items = $i;
            }
            return Response::json($items, 200);
        };

        // Pass the closure to the response hook
        return $this->executeWithHook('onTrashResponse', $defaultResponse, $items);
    }
}
