<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;

class CrudModel extends Model
{

    protected $guarded = ['id', 'save_close', 'save_edit'];

    public static $routeName = '';


    protected $dates = ['deleted_at'];

    public $translatedAttributes = [];

    //this function generate option for action select in header panel
    public function getActions()
    {
        $actions = [
            'index' =>
            [
                'delete' => trans('crud::panel.delete'),
            ],

            'trash' =>
            [
                'restore' => trans('panel.restore'),
            ],
        ];
        return $actions;
    }

    public function getValidation()
    {
        return [];
    }

    public function getFields()
    {
        $fields = [];

        return json_decode(json_encode($fields));
    }

    public function getAdditionalFields()
    {
        $fields = [];
        return json_decode(json_encode($fields));
    }

}
