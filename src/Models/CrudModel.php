<?php

namespace Tir\Crud\Models;

use Illuminate\Database\Eloquent\Model;


class CrudModel extends Model
{

    protected $guarded = ['id', 'save_close'];

    public $timestamps = false;

    protected $dates = ['deleted_at'];

    


}
