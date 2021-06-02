<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;

class CrudModel extends Model
{

    protected $dates = ['deleted_at'];

}
