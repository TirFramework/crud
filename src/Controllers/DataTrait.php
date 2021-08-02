<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;


trait DataTrait
{

    public function data()
    {


    }






    /**
     * This function return a eloquent select with relation ship
     */
//    public function dataQuery($relation): object
//    {
//        $items = $this->model->select($this->model->getTable() . '.*')->with($relation);
//        return $items;
//    }


//    public function getRelationFields($model): array
//    {
//        $relations = [];
//        foreach ($model->getIndexFields() as $field) {
//            if ($field->type == 'oneToMany') {
//                array_push($relations, $field->relationName);
//            }
//        }
//
//        return $relations;
//    }












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

