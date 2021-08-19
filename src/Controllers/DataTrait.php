<?php

namespace Tir\Crud\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Tir\Blog\Entities\Post;


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

    private function applyFilters($query)
    {
        $filters = $this->getFilter();
        foreach ($filters['original'] as $key => $value) {
            return $query->where($key, $value);
        }

        foreach ($filters['relational'] as $filter){
            $query->whereHas($filter['relation'], function (Builder  $q)use($filter){
                $q->whereIn($filter['primaryKey'], $filter['value']);
            });
        }
        return $query;

    }


    private function getFilter():array
    {
        $req = request()->all();
        $filters =['original'=>[],'relational'=>[]];

        foreach ($this->model->getFilterableFields() as $filter) {
            if (array_key_exists($filter->name, $req)) {

                //if filter is manyToMany relation
                if(isset($filter->relation) && isset($filter->multiple))
                {
                    //get table name from relation
                    $table = $this->model->{$filter->relation->name}()->getRelated()->getTable();

                    //get primary key from relation
                    $primaryKey = $this->model->{$filter->relation->name}()->getRelated()->getKeyName();

                    $primaryKey = $table . '.' . $primaryKey;

                    array_push($filters['relational'], ['relation' =>  $filter->relation->name,  'value'=>[$req[$filter->name]], 'primaryKey'=>$primaryKey]);
                }else{
                    $filters['original'][$filter->name] = $req[$filter->name];
                }
            }
        }
        return $filters;
    }

}


