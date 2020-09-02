<?php
namespace Tir\Crud\Requests;

use Tir\Crud\Controllers\CrudController;
use Tir\Crud\Requests\BaseRequest;
use Yajra\DataTables\Facades\DataTables;


class DataRequest extends BaseRequest
{

    /**
     * This function initial and search in fields they mush show in index page,
     *  if filed->visible contain "i" character and field->relation had been true,
     * push field name into $field->relations array for update crud relations.
     * @return void
     */
    public function initialFields()
    {
        foreach ($this->fields as $field){
            if((strpos($field->visible, 'i') == false)){
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
    public function query()
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
    public function datatable($items)
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn=null;
                // if($this->checkPermission('show')){
                //     $viewBtn = '<a href="'.route( $this->name.'.show',$item->getKey()).'" class="btn btn-sm btn-info"><i class="fas fa-eye-open"></i> <span class="hidden">'.trans('panel.view').'</span></a>';
                // }
                if($this->checkPermission('edit')){
                    $editBtn = '<a href="'.route( $this->name.'.edit',$item->getKey()).'" class="btn btn-sm btn-info"><i class="fas fa-edit"></i> <span class="hidden">'.trans('panel.edit').'</span></a>';
                }
                if($this->checkPermission('delete')){
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.destroy', $item->getKey()) . "'" . ')" class="btn btn-sm btn-danger"> <i class="fas fa-trash"></i> <span class="hidden">' . trans('panel.delete') . '</span></button>';
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
        $this->initialFields();
        $items = $this->query();
        return $this->datatable($items);
    }

}
