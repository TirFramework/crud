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
            if (isset($field->relation)) {
                array_push($relations, $field->relation->name);
            }
        }

        return $relations;
    }


    /**
     * This function return a eloquent select with relation ship
     */
    public function dataQuery($relation): object
    {
        $query = $this->model->select($this->model->getTable() . '.*')->with($relation);
        $query = $this->applyFilters($query);
        return $query;
    }

    private function applyFilters($query){
        $filters = request()->all();
        foreach ($filters as $filter => $value)
        {
            if($this->isFilter($filter)){
                $query->where($filter,$value);
            }
        }
        return $query;

    }

    private function isFilter($filter): bool
    {
        if($filter == 'api_token'){
            return false;
        }
        return true;
    }

}

