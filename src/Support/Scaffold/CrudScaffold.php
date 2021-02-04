<?php
namespace Tir\Crud\Support\Scaffold;


trait CrudScaffold
{

    private Fields $fields;

    public function __construct()
    {
        parent::__construct();
        $this->fields = new Fields;
    }
    //this function generate option for action select in header panel
//    public function getActions()
//    {
//        $actions = [
//            'index' =>
//                [
//                    'delete' => trans('crud::panel.delete'),
//                ],
//
//            'trash' =>
//                [
//                    'restore' => trans('panel.restore'),
//                ],
//        ];
//        return $actions;
//    }
//
//    public function getValidation()
//    {
//        return [];
//    }

    public function fields()
    {
        return $this->fields;
    }

//    public function getAdditionalFields()
//    {
//        $fields = [];
//        return $fields;
//    }
}