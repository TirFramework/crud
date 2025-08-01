<?php

namespace Tir\Crud\Support;

trait HasCrudHooks
{
    protected array $crudHookCallbacks = [];

    protected function crudHooks(CrudHooks $hooks): void
    {
        $this->crudHookCallbacks = $hooks->getHooks();
    }

    /**
     * Set custom initQuery hook
     */
    protected function onInitQuery($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifyInitQuery'] = $callback;
        } else {
            // Direct Builder - wrap it in a closure
            $this->crudHookCallbacks['modifyInitQuery'] = fn() => $callback;
        }
        return $this;
    }

    /**
     * Set custom search hook
     */
    protected function onSearch($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifySearch'] = $callback;
        } else {
            // Direct closure for search
            $this->crudHookCallbacks['modifySearch'] = fn($query) => $callback;
        }
        return $this;
    }

    /**
     * Set custom filters hook
     */
    protected function onFilters(callable $callback): self
    {
        $this->crudHookCallbacks['modifyFilters'] = $callback;
        return $this;
    }

    /**
     * Set custom sort hook
     */
    protected function onSort($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifySort'] = $callback;
        } else {
            $this->crudHookCallbacks['modifySort'] = fn($query) => $callback;
        }
        return $this;
    }

    /**
     * Set custom relations hook
     */
    protected function onRelations($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifyRelations'] = $callback;
        } else {
            $this->crudHookCallbacks['modifyRelations'] = fn($query) => $callback;
        }
        return $this;
    }

    /**
     * Set custom columns selection hook
     */
    protected function onColumns($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifyColumns'] = $callback;
        } else {
            // For columns, return the array directly
            $this->crudHookCallbacks['modifyColumns'] = fn() => $callback;
        }
        return $this;
    }

    /**
     * Set custom pagination hook
     */
    protected function onPaginate($callback): self
    {
        if (is_callable($callback)) {
            $this->crudHookCallbacks['modifyPaginate'] = $callback;
        } else {
            $this->crudHookCallbacks['modifyPaginate'] = fn($query) => $callback;
        }
        return $this;
    }
}
