<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait IndexTrait
{

    public function index(): JsonResponse
    {
        $cols = [];
        foreach ($this->model()->getIndexFields() as $index => $field) {
            $cols[$index] = [
                'title'     => $field->display,
                'dataIndex' => $this->getName($field),
                'fieldName' => $field->name,
                'valueType' => $field->valueType,
                'comment'   => $field->comment,
                'dataSet' => $field->dataSet,

            ];
            if(count($field->filter)){
                $cols[$index]['filters'] = $field->filter;
            }
        }

        $data = [
            'actions'    => $this->model()->getActionsStatus(),
            'cols'       => $cols,
            'dataRoute'  => route('admin.' . $this->model()->getmoduleName() . '.data'),
            'trashRoute' => route('admin.' . $this->model()->getmoduleName() . '.trashData'),
        ];

        return Response::json($data, '200');
    }

    private function getName($field)
    {
        if(isset($field->relation)){
            return $field->relation->name;
        }else{
            return $field->name;
        }
    }
}
