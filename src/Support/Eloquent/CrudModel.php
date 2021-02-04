<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;

class CrudModel extends Model
{

    protected $guarded = ['id', 'save_close', 'save_edit'];

    public static $routeName = '';


    protected $dates = ['deleted_at'];

    public $translatedAttributes = [];


}
