<?php

namespace Tir\Crud\Controllers;
use Yajra\DataTables\Facades\DataTables;


trait DataTrait
{
    /**
     * This function initial and search in fields they mush show in index page,
     *  if filed->visible contain "i" character and field->relation had been true,
     * push field name into $field->relations array for update crud relations.
     * @return void
     */
    public function dataInitialFields()
    {
        foreach ($this->fields as $field){
            if((strpos($field->visible, 'i') !== false)){
                if(isset($field->relation)){
                    array_push($this->relations,$field->relation);
                }
            }
        }
    }


    /**
     * This function return a eloquent select with relation ship
     * @return eloquent
     */
    public function dataQuery()
    {
        $items = $this->model::select($this->table.'.*')->with($this->relations);
        if($this->permission == 'owner'){
            $items = $items->OnlyOwner();
        }
        return $items;
    }


    /**
     * This function return Datatables with add columsn
     * @param object $items
     * @return  \Yajra\DataTables\Facades\DataTables
     */
    public function dataTable($items)
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn=null;
                if($this->checkPermission('show')){
                    $viewBtn = '<a href="'.route( $this->name.'.show',$item->getKey()). '" class="fa-lg text-success"><i title="' . trans('panel.view') . '" class="fas fa-external-link-alt"></i></a>';
                }
                if($this->checkPermission('edit')){
                    $editBtn = '<a href="'.route( $this->name.'.edit',$item->getKey()). '" class="fa-lg text-info"><i title="' . trans('panel.edit') . '" class="fas fa-pencil-alt"></i></a>';
                }
                if($this->checkPermission('destroy')){
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.destroy', $item->getKey()) . "'" . ')" class="fa-lg text-danger"> <i title="' . trans('panel.delete') . '" class="fas fa-trash"></i></button>';
                }
                return $viewBtn.' '.$editBtn.' '.$DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

    /**
     * Add extra column to datatable
     * @return array
     */
    public function addColumns()
    {
        return [];
    }

    /**
     * return datatable object
     * @return object
     */
    public function data()
    {
        $this->dataInitialFields();
        $items = $this->dataQuery();
        return $this->dataTable($items);
    }



        /**
     * return datatable object
     * @return object
     */
    public function trashData()
    {
        $this->dataInitialFields();
        $items = $this->dataQuery()->onlyTrashed();
        return $this->trashDataTable($items);
    }

    /**
     * This function return Datatables with add columsn
     * @param object $items
     * @return  \Yajra\DataTables\Facades\DataTables
     */
    public function trashDataTable($items)
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $DeleteBtn = $restoreBtn=null;

                if($this->checkPermission('destroy')){
                    $restoreBtn = '<a href="'.route( $this->name.'.restore',$item->getKey()).'" class="btn btn-sm btn-success"><i class="fas fa-repeat"></i> <span class="hidden">'.trans('panel.restore').'</span></a>';
                }
                if($this->checkPermission('forceDestroy')){
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.forceDestroy', $item->getKey()) . "'" . ')" class="btn btn-sm btn-danger"> <i class="fas fa-trash"></i> <span class="hidden">' . trans('panel.delete') . '</span></button>';
                }
                return $restoreBtn.' '.$DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

}

