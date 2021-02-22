<?php
namespace Tir\Crud\Support\Scaffold;


abstract Class CrudScaffold
{

    protected abstract static function name();
    protected abstract static function model();
    protected abstract static function fields();

    protected static function routeName()
    {
        return static::name();
    }


    /**
     * @return array
     */
    final static function getFields(){
        Fields::add(static::fields());
        return Fields::get();
    }

    /**
     * @return string
     */
    final static function getCrudName()
    {
        return static::name();
    }

    /**
     * @return string
     */
    final static function getModel()
    {
        return static::model();
    }

    /**
     * @return string
     */
    final static function getRouteName(): string
    {
        return static::routeName();
    }


}