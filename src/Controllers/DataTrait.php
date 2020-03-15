<?php 

namespace Tir\Crud\Controllers;

use Yajra\DataTables\Facades\DataTables;


trait DataTrait
{
    private $dataRelations = [];
    public function initialFields()
    {
        foreach ($this->fields as $field){
            if((strpos($field->visible, 'i') == false)){
                if(isset($field->relation)){
                    array_push($this->dataRelations,$field->relation);
                }
            }
        }
    }

    public function query()
    {
        $items = $this->model::select($this->table.'.*')->with($this->dataRelations);
        if($this->permission == 'owner'){
            $items = $items->OnlyOwner();
        }
        return $items;
    }

    public function datatable($items)
    {
        return Datatables::eloquent($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn=null;
                if($this->checkPermission('show')){
                    $viewBtn = '<a href="'.route( $this->name.'.show',$item->getKey()).'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> <span class="hidden">'.trans('panel.view').'</span></a>';
                }
                if($this->checkPermission('edit')){
                    $editBtn = '<a href="'.route( $this->name.'.edit',$item->getKey()).'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-edit"></i> <span class="hidden">'.trans('panel.edit').'</span></a>';
                }
                if($this->checkPermission('delete')){
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.destroy', $item->getKey()) . "'" . ')" class="btn btn-sm btn-danger"> <i class="glyphicon glyphicon-trash"></i> <span class="hidden">' . trans('panel.delete') . '</span></button>';
                }
                return $viewBtn.' '.$editBtn.' '.$DeleteBtn;
            })->addColumns($this->addColumns())
            ->make(true);
    }

    public function addColumns()
    {
        return [];
    }

    public function data()
    {
        $this->initialFields();
        $items = $this->query();
        return $this->datatable($items);
    }

}