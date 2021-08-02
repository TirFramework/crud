<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;


trait DataTrait
{

    /**
     * return datatable object
     * @return object
     * @throws \Exception
     */
    public function data(): object
    {
        $relations = $this->getRelationFields($this->model);
        $items = $this->dataQuery($relations);
        return $this->dataTable($items);
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
        $items = $this->model->select($this->model->getTable() . '.*')->with($relation);
        return $items;
    }


    /**
     *
     * @throws \Exception
     */
    public function dataTable($items): JsonResponse
    {

        $permission = $this->getDataPermission();


        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) use ($permission) {
                $viewBtn = $DeleteBtn = $editBtn = null;
                if ($permission['index'] != 'deny') {
                    $viewBtn = '<a href="' . route('admin.' . $this->model->getModuleName() . '.show', $item->getKey()) . '" class="fa-md text-success"><i title="' . trans('panel.view') . '" class="far fa-eye"></i></a>';
                }
                if ($permission['edit']) {
                    $editBtn = '<a href="' . route('admin.' . $this->model->getModuleName() . '.edit', $item->getKey()) . '" class="fa-md text-info"><i title="' . trans('panel.edit') . '" class="fas fa-pencil-alt"></i></a>';
                }
                if ($permission['destroy']) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route('admin.' . $this->model->getModuleName() . '.destroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $viewBtn . ' ' . $editBtn . ' ' . $DeleteBtn;
            })->addColumn('data', $this->tableData($items))
            ->addColumns($this->addColumns())
            ->make(true);
    }


    /**
     * Add extra column to datatable
     * @return array
     */
    public function addColumns(): array
    {
        return [];
    }

    private function tableData($items)
    {

        $col = null;
        $filters = null;

        //if enable drag reorder and add column $loop  must be equals to 1
        $loop = 0;
        $responsive = true;
        $className = null;
        $orderField = 0;
        foreach ($this->model->getIndexFields() as $field) {
            $name = $field->name;
            $key = $this->model->getTable() . '.' . $field->name;
            $render = null;
            $searchable = 'true';

            if ($field->type == 'oneToMany'):     //relationship must have datatable field for show in datatable
                $name = $key = $field->relationName . '.' . $field->relationKey;
            endif;

            //for many to many datatable $field->datatable must be array and have two index ,first is name and second is data
            if ($field->type == 'manyToMany'):

                $relationModel = get_class($this->model->{$field->relation[0]}()->getModel());
                $dataModel = new  $relationModel;
                $dataField = $field->relation[1];
                $name = $field->relation[0] . '[ , ].' . $field->relation[1];
                $key = $field->relation[0] . '.' . $field->relation[1];
            endif;

            if ($field->type == 'position'):
                $className = ",className:'position'";
                $orderField = $loop;
            endif;

            //add searchable item
            if (isset($field->searchable)) {
                if ($field->searchable == false || $field->searchable == 'false') {
                    $searchable = 'false';
                }
            }
            $col .= "{ data:`$name`, name: `$key` $className, defaultContent: '' $render, searchable: $searchable},";


            //filters
            //translated fields can not filter
//                        if(strpos($field->visible, 'f') !== false){
//                            if($field->type == 'relation' || $field->type == 'relationM'){
//
//                                $relationModel =  get_class($model->model->{$field->relation[0]}()->getModel());
//                                $dataModel = new  $relationModel;
//                                $dataField = $field->relation[1];
//
//                                //check relation model field not translated
//                                if(in_array($dataField, $dataModel->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($model->name))->select($dataField)->distinct($dataField)->pluck($dataField)).', ';
//                                }else{
//                                    $filters .= $loop.':'.json_encode($dataModel::has(Str::plural($model->name))->select('*')->get()->pluck($dataField)).', ';
//                                        if($field->type == 'relationM'){
//                                            $filters .= $loop.':["disabled in many to many translation"], ';
//                                        }
//
//                                }
//                            }else{
//                                if( in_array($field->name, $model->model->translatedAttributes) == false){
//                                    $filters .= $loop.':'.json_encode($model->model::select($field->name)->distinct($field->name)->pluck($field->name)).', ';
//                                }else{
//                                   $filters .= $loop.':'.json_encode($model->model::select('*')->get()->pluck($field->name)).', ';
//                                }
//                            }
//                         }
            $loop++;
        }
        $data = [
            'col'        => $col,
            'filter'     => '',
            'dataRoute'  => route('admin.' . $this->model->moduleName . '.data'),
            'trashRoute' => route('admin.' . $this->model->moduleName . '.trashData'),
        ];
        return $data;
    }


    /**
     * return datatable object
     * @return object
     * @throws \Exception
     */
    public function trashData(): object
    {
        $relations = $this->getRelationFields($this->model);
        $items = $this->dataQuery($relations)->onlyTrashed();
        return $this->trashDataTable($items);
    }


    /**
     * @throws \Exception
     */
    public function trashDataTable($items): JsonResponse
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $DeleteBtn = $restoreBtn = null;

                if ($this->checkAccess($this->model->getModuleName(), 'destroy')) {
                    $restoreBtn = '<a href="' . route($this->model->getModuleName() . '.restore', $item->getKey()) . '" class="fa-md text-success"><i title="' . trans('panel.restore') . '" class="fas fa-recycle"></i></a>';

                }
                if ($this->checkAccess($this->model->getModuleName(), 'forceDestroy')) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->model->getModuleName() . '.forceDestroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $restoreBtn . ' ' . $DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

}

