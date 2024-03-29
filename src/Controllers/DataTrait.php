<?php

namespace Tir\Crud\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Arr;


trait DataTrait
{

    private array $selectFields =[];

    public function data(): JsonResponse
    {
        $relations = $this->getRelationFields($this->model());
        $items = $this->dataQuery($relations);
        $paginatedItems = $items->orderBy('created_at','DESC')->paginate(request()->input('result'));
        return Response::Json($paginatedItems, '200');
    }


    public function getRelationFields($model): array
    {
        $relations = [];
        foreach ($model->getIndexFields() as $field) {
            if (isset($field->relation)) {
//                $relation = $field->relation->name . ':' . $field->relation->key . ',' . $field->relation->field. ' as text';
                $relation = $field->relation->name . ':' . $field->relation->key;

                if($model->getConnection()->getName() == 'mongodb'){
                    $relation = $field->relation->name;
                }
                $relations[] = $relation;
            }
        }

        return $relations;
    }


    /**
     * This function return an eloquent select with relationship
     */
    public function dataQuery($relation): object
    {



        $query = $this->model()->select($this->model()->getTable() . '.*')->with($relation);

        if($this->model()->getConnection()->getName() == 'mongodb') {
            $this->selectFields = array_merge($this->selectFields, collect($this->model()->getIndexFields())->pluck('name')->toArray());
            $query = $this->model()->select($this->selectFields)->with($relation);
        }

        $query = $this->applySearch($query);

        return $this->applyFilters($query);
    }

    public function applySearch($query)
    {
        $req = request()->input('search');
        if($req == null){
            return $query;
        }
        $searchableFields = $this->model()->getSearchableFields();

        foreach($searchableFields as $field){
            $query->orWhere($field->name,'like', "%$req%");
        }

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
            $field = $this->model()->getFieldByName($filter);

            //if filter is manyToMany relation
            if(isset($field->relation) && $field->multiple ?? null == true)
            {
                //get table name from relation
                $table = $this->model()->{$field->relation->name}()->getRelated()->getTable();

                //get primary key from relation
                $primaryKey = $this->model()->{$field->relation->name}()->getRelated()->getKeyName();

                $primaryKey = $table . '.' . $primaryKey;

                if($this->model()->getConnection()->getName() == 'mongodb'){
                    $primaryKey = $this->model()->{$field->relation->name}()->getRelated()->getKeyName();
                }

                $relational[] = ['relation' => $field->relation->name, 'value' => $value, 'primaryKey' => $primaryKey];
            }else{
                $original[$field->name] = $req->{$field->name};
            }
        }

        $filters['original'] = $original;
        $filters['relational'] = $relational;

        return $filters;
    }
}


