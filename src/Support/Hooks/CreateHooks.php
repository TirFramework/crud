<?php

namespace Tir\Crud\Support\Hooks;

trait CreateHooks
{
    use BaseHooks;

    /**
     * Set custom hook for create form generation
     *
     * @param callable(callable $defaultCreate): mixed $callback
     */
    protected function onCreate(callable $callback): self
    {
        $this->crudHookCallbacks['onCreate'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for create response
     *
     * @param callable(callable $defaultResponse, mixed $fields): mixed $callback
     */
    protected function onCreateResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onCreateResponse'] = $callback;
        return $this;
    }
}
