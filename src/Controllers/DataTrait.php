<?php

namespace Tir\Crud\Controllers;

use App\Panels\Admin\Models\Candidate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Arr;
use Tir\Crud\Support\Enums\FilterType;


trait DataTrait
{

    private array $selectFields =[];

    public function data()
    {
        $relations = $this->getRelationFields($this->model());
        $items = $this->dataQuery($relations);
        $paginatedItems = $items->paginate(request()->input('result'));
//        $paginatedItems = $items->orderBy('created_at','ASC')->take(1)->get();

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


    public function getRelations($query)
    {
        foreach ($this->model()->getIndexFields() as $field) {
            if(isset($field->relation)) {
                if($this->model()->getConnection()->getName() == 'mongodb') {
                    if($field->multiple){
                        // mongoDB need foreign key in many-to-many relation
                        $foreignKey = $this->model()->{$field->relation->name}()->getForeignKey();
                        $otherKey = $this->model()->{$field->relation->name}()->getRelated()->getKeyName();
                        $query = $query->with($field->relation->name, function ($q) use ($field, $foreignKey, $otherKey) {
                            $q->select($foreignKey, $otherKey, $field->relation->field);
                        });
                    }else {
                        $otherKey = $this->model()->{$field->relation->name}()->getRelated()->getKeyName();
                        $query = $query->with($field->relation->name, function ($q) use ($field, $otherKey) {
                            $q->select($otherKey, $field->relation->field);
                        });
                    }
                }else{
                    $query = $query->with($field->relation->name.':'.$field->relation->field);
                }
            }
        }
        return $query;
    }

    /**
     * This function return an eloquent select with relationship
     */
    public function dataQuery($relation): object
    {

        if($this->model()->getConnection()->getName() == 'mongodb') {
            $this->selectFields = array_merge($this->selectFields, collect($this->model()->getIndexFields())->pluck('name')->toArray());
        }else{
            $this->selectFields[] = $this->model()->getTable().'.'.$this->model()->getKeyName();
            foreach ($this->model()->getIndexFields() as $field) {
                //Check if field is many to many relation or not
                if(!isset($field->relation) || !$field->multiple){
                    $this->selectFields[] = $this->model()->getTable().'.'.$field->name;
                }
            }
        }

        $query = $this->model->select($this->selectFields);

        $query = $this->getRelations($query);
        $query = $this->applySearch($query);

        $query = $this->applyFilters($query);

        $query = $this->applySort($query);

        return $query;
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
        foreach ($filters['original'] as $filter) {
            if($filter['type'] == FilterType::Select) {
                $query->whereIn($filter['column'], $filter['value']);
            }elseif ($filter['type'] == FilterType::Slider) {
                $query->where($filter['column'], '>=', $filter['value'][0]);
                $query->where($filter['column'], '<=', $filter['value'][1]);
            }elseif ($filter['type'] == FilterType::DatePicker) {
                if($this->model()->getConnection()->getName() == 'mongodb'){

                    $query->where(function($query) use ($filter){
                        $query->where($filter['column'], '>=', new \MongoDB\BSON\UTCDateTime(Carbon::make($filter['value'][0])->startOfDay()));
                        $query->orWhere($filter['column'], '>=', Carbon::make($filter['value'][0])->startOfDay()->toDateString());
                    });
                    $query->where(function($query) use ($filter){
                        $query->where($filter['column'], '<=', new \MongoDB\BSON\UTCDateTime(Carbon::make($filter['value'][1])->endOfDay()));
                        $query->orWhere($filter['column'], '<=', Carbon::make($filter['value'][1])->endOfDay()->toDateString());
                    });

                }else {
                    $query->whereDate($filter['column'], '>=', Carbon::make($filter['value'][0])->startOfDay());
                    $query->whereDate($filter['column'], '<=', Carbon::make($filter['value'][1])->endOfDay());
                }
            }elseif($filter['type'] == FilterType::Search) {
                $query->where($filter['column'], 'like', "%".$filter['value']."%");
            }
        }

        foreach ($filters['relational'] as $filter){
            $query->whereHas($filter['relation'], function (Builder  $q)use($filter){
                $q->whereIn($filter['primaryKey'], $filter['value']);
            });
        }
        return $query;

    }


    public function applySort($query)
    {
        $req = request()->input('sorter');
        if($req == null){
            return $query->orderBy('created_at','DESC');
        }

        $sort = json_decode($req);

        if(!isset($sort->field)){
            return $query->orderBy('created_at','DESC');
        }
        $sort->order = $sort->order == 'ascend' ? 'ASC' : 'DESC';
        $query->orderBy($sort->field, $sort->order);
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
            }
            else{
                $original[] = ['column'=> $field->name, 'value'=> $req->{$field->name}, 'type'=> $field->filterType];
            }
        }

        $filters['original'] = $original;
        $filters['relational'] = $relational;

        return $filters;
    }
}


