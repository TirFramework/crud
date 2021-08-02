<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait IndexTrait
{

    public function index(): JsonResponse
    {

        $col = [];
        $filters = null;


        foreach ($this->model->getIndexFields() as $index => $field) {
            $col[$index] = [
                'title'     => $field->display,
                'dataIndex' => $field->name,
                'sorter'    => $field->sortable,
            ];
        }


        $data = [
            'cols'       => $col,
            'dataRoute'  => route('admin.' . $this->model->moduleName . '.data'),
            'trashRoute' => route('admin.' . $this->model->moduleName . '.trashData'),
        ];

        return Response::json($data);
    }
}
