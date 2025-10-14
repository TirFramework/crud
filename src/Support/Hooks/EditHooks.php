<?php

namespace Tir\Crud\Support\Hooks;

trait EditHooks
{
    use BaseHooks;

    /**
     * Set custom hook for edit form generation
     *
     * @param callable(callable $defaultEdit, string|int $id): mixed $callback
     */
    protected function onEdit(callable $callback): self
    {
        $this->crudHookCallbacks['onEdit'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for edit response
     *
     * @param callable(callable $defaultResponse, mixed $dataModel): mixed $callback
     */
    protected function onEditResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onEditResponse'] = $callback;
        return $this;
    }
}
