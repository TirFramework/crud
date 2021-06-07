<?php


namespace Tir\Crud\Support\Eloquent;


use Illuminate\Database\Eloquent\Model;
use Tir\Crud\Support\Scaffold\BaseScaffold;

abstract class BaseModel extends Model
{
    use BaseScaffold;

    protected $dates = ['deleted_at'];

}