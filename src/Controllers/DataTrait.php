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
        $model = new $this->model;
        $this->scaffoldName = $model->getScaffoldName();
        $relations = $this->getRelationFields($model);
        $items = $this->dataQuery($model, $relations);
        return $this->dataTable($model, $items);
    }


    public function getRelationFields($model)
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
    public function dataQuery($model, $relation): object
    {
        $items = $model->select($model->getTable() . '.*')->with($relation);
        if ($this->checkAccess($this->scaffoldName, 'index') == 'owner') {
            $items = $items->OnlyOwner();
        }
        return $items;
    }


    /**
     *
     * @throws \Exception
     */
    public function dataTable($model, $items): JsonResponse
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn = null;
                if ($this->checkAccess($this->scaffoldName, 'index') != 'deny') {
                    $viewBtn = '<a href="' . route('admin.' . $this->scaffoldName . '.show', $item->getKey()) . '" class="fa-md text-success"><i title="' . trans('panel.view') . '" class="far fa-eye"></i></a>';
                }
                if ($this->checkAccess($this->scaffoldName, 'edit') != 'deny') {
                    $editBtn = '<a href="' . route('admin.' . $this->scaffoldName . '.edit', $item->getKey()) . '" class="fa-md text-info"><i title="' . trans('panel.edit') . '" class="fas fa-pencil-alt"></i></a>';
                }
                if ($this->checkAccess($this->scaffoldName, 'destroy') != 'deny') {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route('admin.' . $this->scaffoldName . '.destroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
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
     * @throws \Exception
     */
    public function trashData(): object
    {
        $this->getRelationFields();
        $items = $this->dataQuery()->onlyTrashed();
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

                if ($this->checkAccess('destroy')) {
                    $restoreBtn = '<a href="' . route($this->crud->name . '.restore', $item->getKey()) . '" class="fa-md text-success"><i title="' . trans('panel.restore') . '" class="fas fa-recycle"></i></a>';

                }
                if ($this->checkAccess('forceDestroy')) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->crud->name . '.forceDestroy', $item->getKey()) . "'" . ')" class="fa-md text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $restoreBtn . ' ' . $DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

}

