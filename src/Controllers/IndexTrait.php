<?php 

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Events\CrudIndex;
use Yajra\DataTables\Facades\DataTables;


trait IndexTrait
{
    /**
     * This function call indexCrud method and run Index event
     * @return $this->indexCrud();
     */
    public function index()
    {
        event(new CrudIndex($this->name));
        return $this->indexCrud();

    }
    
    /**
     * This function return and pass crud value to the index view.
     * @return \Illuminate\Support\Facades\View index
     */
    public function indexCrud()
    {
        return View::first(["$this->name::admin.index", "crud::scaffold.index"])->with('crud', $this->crud);
    }
    
    /**
     * Create datatable object from crud model
     * @return Object
     */
    public function data()
    {
        //event(new CrudIndex($this->name));
        return $this->dataCrud();
    }

    public function dataCrud()
    {
        $permission = 'all';
        foreach ($this->fields as $field){
            if((strpos($field->visible, 'i') !== false)){
                if(isset($field->relation)){
                    array_push($this->relations,$field->relation);
                }
            }
        }

        if($permission == 'all'){
            $items = $this->model::select($this->table . '.*')->with($this->relations);
        }elseif($permission == 'owner'){
            $items = $this->model::select($this->table . '.*')->with($this->relations)->OnlyOwner();
        }
        return Datatables::of($items)
            ->addColumn('action', function ($item) {
                $viewBtn = $DeleteBtn = $editBtn=null;
                // if(Acl::checkAccess($this->name, 'view')){
                    $viewBtn = '<a href="'.route( $this->name.'.show',$item->getKey()).'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-eye-open"></i> <span class="hidden">'.trans('panel.view').'</span></a>';
                // }
                // if(Acl::checkAccess($this->name, 'edit')){
                    $editBtn = '<a href="'.route( $this->name.'.edit',$item->getKey()).'" class="btn btn-sm btn-info"><i class="glyphicon glyphicon-edit"></i> <span class="hidden">'.trans('panel.edit').'</span></a>';
                // }
                // if(Acl::checkAccess($this->name, 'delete')) {
                    $DeleteBtn = '<button onclick=' . '"deleteRow(' . "'" . route($this->name . '.destroy', $item->getKey()) . "'" . ')" class="btn btn-sm btn-danger"> <i class="glyphicon glyphicon-trash"></i> <span class="hidden">' . trans('panel.delete') . '</span></button>';
                // }
                return $viewBtn.' '.$editBtn.' '.$DeleteBtn;
            })
            ->make(true);
    }

}