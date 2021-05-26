<?php

namespace Tir\Crud\Controllers;

use Yajra\DataTables\Facades\DataTables;


trait DataTrait
{

    /**
     * return datatable object
     * @return object
     */
    public function data(): object
    {
        $this->getRelationFields();
        $items = $this->dataQuery();
        return $this->dataTable($items);
    }



    public function getRelationFields()
    {
        foreach ($this->crud->indexFields as $field) {
            if ($field->type == 'oneToMany') {
                //get model form relation
                $relationModel = get_class($this->crud->model->{$field->relationName}()->getModel());
                array_push($this->relations, $field->relationName);
            }
        }
    }


    /**
     * This function return a eloquent select with relation ship
     * @return eloquent
     */
    public function dataQuery()
    {
        $model = $this->crud->model;
        $items = $model::select($this->crud->table . '.*')->with($this->relations);
//        if($this->permission == 'owner'){
//            $items = $items->OnlyOwner();
//        }

        return $items;
    }


    public function dataTable($items): \Illuminate\Http\JsonResponse
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn = null;
                 if($this->checkPermission('show')){
                     $viewBtn = '<a href="'.route( $this->crud->name.'.show',$item->getKey()). '" class="fa-md text-success"><i title="' . trans('panel.view') . '" class="far fa-eye"></i></a>';
                 }
                if ($this->checkPermission('edit')) {
                    $editBtn = '<a href="' . route($this->crud->name . '.edit', $item->getKey()) . '" class="fa-md text-info"><i title="' . trans('panel.edit') . '" class="fas fa-pencil-alt"></i></a>';
                }
                if ($this->checkPermission('destroy')) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->crud->name . '.destroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $viewBtn . ' ' . $editBtn . ' ' . $DeleteBtn;
            })->addColumns($this->addColumns())
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



    /**
     * return datatable object
     * @return object
     */
    public function trashData(): object
    {
        $this->getRelationFields();
        $items = $this->dataQuery()->onlyTrashed();
        return $this->trashDataTable($items);
    }


    public function trashDataTable($items)
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $DeleteBtn = $restoreBtn = null;

                if ($this->checkPermission('destroy')) {
                    $restoreBtn = '<a href="' . route($this->name . '.restore', $item->getKey()) . '" class="fa-md text-success"><i title="' . trans('panel.restore') . '" class="fas fa-recycle"></i></a>';

                }
                if ($this->checkPermission('forceDestroy')) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.forceDestroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $restoreBtn . ' ' . $DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

}

