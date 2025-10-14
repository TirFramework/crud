<?php

namespace Tir\Crud\Support\Hooks;

trait IndexDataHooks
{
    use \Tir\Crud\Support\Hooks\BaseHooks;



    /**
     * Set custom hook for initializing query
     *
     * @param callable(callable $defaultInitQuery): mixed $callback
     */
    protected function onInitQuery(callable $callback): self
    {
        $this->crudHookCallbacks['onInitQuery'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for select columns
     *
     * @param callable(callable $defaultSelect, mixed $query): mixed $callback
     */
    protected function onSelect(callable $callback): self
    {
        $this->crudHookCallbacks['onSelect'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for search
     *
     * @param callable(callable $defaultSearch, mixed $query): mixed $callback
     */
    protected function onSearch(callable $callback): self
    {
        $this->crudHookCallbacks['onSearch'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for filters
     *
     * @param callable(callable $defaultFilters, mixed $query): mixed $callback
     */
    protected function onFilter(callable $callback): self
    {
        $this->crudHookCallbacks['onFilter'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for sort
     *
     * @param callable(callable $defaultSort, mixed $query): mixed $callback
     */
    protected function onSort(callable $callback): self
    {
        $this->crudHookCallbacks['onSort'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for relations
     *
     * @param callable(callable $defaultRelations, mixed $query): mixed $callback
     */
    protected function onRelation(callable $callback): self
    {
        $this->crudHookCallbacks['onRelation'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for custom query modifications
     *
     * @param callable(callable $defaultModifiedQuery, mixed $query): mixed $callback
     */
    protected function onModifyQuery(callable $callback): self
    {
        $this->crudHookCallbacks['onModifyQuery'] = $callback;
        return $this;
    }



    /**
     * Set custom hook for pagination
     *
     * @param callable(callable $defaultPagination, mixed $query): mixed $callback
     */
    protected function onPaginate(callable $callback): self
    {
        $this->crudHookCallbacks['onPaginate'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for index response
     *
     * @param callable(callable $defaultResponse, mixed $items): mixed $callback
     */
    protected function onIndexResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onIndexResponse'] = $callback;
        return $this;
    }



}
