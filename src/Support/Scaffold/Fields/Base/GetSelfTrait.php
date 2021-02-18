<?php


namespace Tir\Crud\Support\Scaffold\Inputs;


trait GetSelfTrait
{
    protected  $onlyInstance;

    /**
     * Create an instance of static class
     * This function help to use chain method in static methods
     * @return mixed
     */
    protected  function getSelf()
    {
        if ($this->onlyInstance === null)
        {
            $this->onlyInstance = new $this;
        }

        return $this->onlyInstance;
    }

}