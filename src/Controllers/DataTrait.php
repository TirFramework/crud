<?php

namespace Tir\Crud\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Tir\Blog\Entities\Post;
use function PHPUnit\Framework\isEmpty;


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
        $req = json_decode(request()->input('filters'));
        if($req == null){
            return $query;
        }

        $filters = $this->getFilter($req);



        foreach ($filters['original'] as $key => $value) {
            $query->whereIn($key, $value);
        }

        foreach ($filters['relational'] as $filter){
            $query->whereHas($filter['relation'], function (Builder  $q)use($filter){
                $q->whereIn($filter['primaryKey'], $filter['value']);
            });
        }
        return $query;

    }


    private function getFilter($req):array
    {

        $filters =['original'=>[],'relational'=>[]];

        $original = [];
        $relational = [];

        foreach ($req as $filter => $value) {
            $field = $this->model->getFieldByName($filter);

                //if filter is manyToMany relation
                if(isset($field->relation) && isset($field->multiple))
                {
                    //get table name from relation
                    $table = $this->model->{$field->relation->name}()->getRelated()->getTable();

                    //get primary key from relation
                    $primaryKey = $this->model->{$field->relation->name}()->getRelated()->getKeyName();

                    $primaryKey = $table . '.' . $primaryKey;

                    array_push($relational, ['relation' =>  $field->relation->name,  'value'=>$value, 'primaryKey'=>$primaryKey]);
                }else{
                    $original[$field->name] = $req->{$field->name};
                }
            }

        $filters['original'] = $original;
        $filters['relational'] = $relational;

        return $filters;
    }

}


