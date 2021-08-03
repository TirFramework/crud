<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;


trait DataTrait
{

    public function data(): JsonResponse
    {
        $relations = $this->getRelationFields($this->model);
        $items = $this->dataQuery($relations);

        return Response::Json($items->paginate(), '200');

    }


    public function getRelationFields($model): array
    {
        $relations = [];
        foreach ($model->getIndexFields() as $field) {
            if ($field->type == 'oneToMany') {
                array_push($relations, $field->relationName);
            }
        }

        return $relations;
    }


    /**
     * This function return a eloquent select with relation ship
     */
    public function dataQuery($relation): object
    {
        return $this->model->select($this->model->getTable() . '.*')->with($relation);
    }


}

