<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait IndexTrait
{

    public function index()
    {
        $col = [];
        foreach ($this->model->getIndexFields() as $index => $field) {
            $col[$index] = [
                'title'     => $field->display,
                'dataIndex' => $this->getName($field),
                'sorter'    => $field->sortable,
                'fieldName' => $field->name,
            ];
            if(count($field->filter)){
                $col[$index]['filters'] = $field->filter;
            }
        }


        $data = [
            'cols'       => $col,
            'dataRoute'  => route('admin.' . $this->model->moduleName . '.data'),
            'trashRoute' => route('admin.' . $this->model->moduleName . '.trashData'),
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
