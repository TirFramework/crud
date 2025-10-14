<?php

namespace Tir\Crud\Support\Hooks;

trait TrashHooks
{
    use BaseHooks;

    /**
     * Set custom hook for trash data retrieval
     *
     * @param callable(callable $defaultTrash): mixed $callback
     */
    protected function onTrash(callable $callback): self
    {
        $this->crudHookCallbacks['onTrash'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for trash response
     *
     * @param callable(callable $defaultResponse, mixed $items): mixed $callback
     */
    protected function onTrashResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onTrashResponse'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for trash select query
     *
     * @param callable(callable $defaultSelect, mixed $query): mixed $callback
     */
    protected function onTrashSelect(callable $callback): self
    {
        $this->crudHookCallbacks['onTrashSelect'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for trash filter
     *
     * @param callable(callable $defaultFilter, mixed $query): mixed $callback
     */
    protected function onTrashFilter(callable $callback): self
    {
        $this->crudHookCallbacks['onTrashFilter'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for trash sort
     *
     * @param callable(callable $defaultSort, mixed $query): mixed $callback
     */
    protected function onTrashSort(callable $callback): self
    {
        $this->crudHookCallbacks['onTrashSort'] = $callback;
        return $this;
    }
}
