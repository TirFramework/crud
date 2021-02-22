<?php

namespace Tir\Crud\Support\Scaffold\Fields;


abstract Class BaseField
{

    protected string $type;
    protected string $name;
    protected string $visible;
    protected string $display;
    protected string $placeholder;
    protected bool $disable;
    protected string $defaultValue;
    protected bool $showOnIndex = true;
    protected bool $showOnDetail = true;
    protected bool $showOnCreate = true;
    protected bool $showOnEdit = true;
    protected bool $sortable;

    /**
     * Add name attribute to input
     *
     * @param string $name It will be name of input field
     * @return $this
     */
    public static function make(string $name)
    {
        $obj = new static;
        $obj->name = $name;
        return $obj;
    }


    /**
     * Add display attribute to input
     *
     * @param string $value It will be display attribute of input
     * @return $this
     */
    public function display(string $value)
    {

        $this->display = $value;
        return $this;
    }

    /**
     * Add display class to input
     *
     * @param string $name
     * @return $this
     */
    public function class(string $name)
    {
        $this->class = $name;
        return $this;
    }

    /**
     * Add display id to input
     *
     * @param string $name
     * @return $this
     */
    public function id(string $name)
    {
        $this->id = $name;
        return $this;
    }

    /**
     * Add placeholder attribute to input
     *
     * @param string $text
     * @return $this
     */
    public function placeholder(string $text)
    {
        $this->placeholder = $text;
        return $this;
    }

    /**
     * Add disable attribute to input
     *
     * @param bool $option
     * @return $this
     */
    public function disable(bool $option)
    {
        $this->disable = $option;
        return $this;
    }


    /**
     * Add value attribute to input
     *
     * @param string $value
     * @return $this
     */
    public function default(string $value)
    {
        $this->defaultValue = $value;
        return $this;
    }


    /**
     * @param bool $check
     * @return $this
     */
    public function showOnIndex($check = true)
    {
        $this->showOnIndex = $check;
        return $this;
    }

    /**
     * @param bool $check
     * @return $this
     */
    public function showOnCreate($check = true)
    {
        $this->showOnCreate = $check;
        return $this;
    }


    /**
     * @param bool $check
     * @return $this
     */
    public function showOnEdit($check = true)
    {
        $this->showOnEdit = $check;
        return $this;
    }


    /**
     * @param bool $check
     * @return $this
     */
    public function showOnDetail($check = true)
    {
        $this->showOnIndex = $check;
        return $this;
    }


    /**
     * @param bool $callback
     * @return $this
     */
    public function hideOnIndex($callback = true)
    {
        $this->showOnIndex = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }


    /**
     * @param bool $callback
     * @return $this
     */
    public function hideFromCreate($callback = true)
    {
        $this->showOnCreate = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }


    /**
     * @param bool $callback
     * @return $this
     */
    public function hideFromEdit($callback = true)
    {
        $this->showOn = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }


    /**
     * @param bool $callback
     * @return $this
     */
    public function hideFromDetail($callback = true)
    {
        $this->showOnDetail = is_callable($callback) ? !call_user_func_array($callback, func_get_args())
            : !$callback;
        return $this;
    }


    /**
     * @param bool $check
     * @return $this
     */
    public function sortable(bool $check = true)
    {
        $this->sortable = $check;
        return $this;

    }


    /**
     * @return array
     */
    public function get()
    {
        return get_object_vars($this);
    }

}
