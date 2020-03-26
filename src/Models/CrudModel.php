<?php

namespace Tir\Crud\Models;

use Illuminate\Database\Eloquent\Model;

class CrudModel extends Model
{

    protected $guarded = ['id', 'save_close'];

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    //this function generate option for action select in header panel
    public function getActions()
    {
        $actions = [
            'index' =>
            [
                'published' => trans('crud::panel.publish'),
                'unpublished' => trans('crud::panel.unpublish'),
                'draft' => trans('crud::panel.draft'),
                'delete' => trans('crud::panel.delete'),
            ],

            'trash' =>
            [
                'restore' => trans('panel.restore'),
                'fullDelete' => trans('panel.full_delete'),
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
