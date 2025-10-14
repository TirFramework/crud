<?php

namespace Tir\Crud\Support\Hooks;

trait RestoreHooks
{
    use BaseHooks;

    /**
     * Set custom hook for restore operation
     *
     * @param callable(callable $defaultRestore, string|int $id): mixed $callback
     */
    protected function onRestore(callable $callback): self
    {
        $this->crudHookCallbacks['onRestore'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for restore response
     *
     * @param callable(callable $defaultResponse, mixed $restoredItem): mixed $callback
     */
    protected function onRestoreResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onRestoreResponse'] = $callback;
        return $this;
    }
}
