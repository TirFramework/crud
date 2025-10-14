<?php

namespace Tir\Crud\Support\Hooks;

trait RequestHooks
{
    use \Tir\Crud\Support\Hooks\BaseHooks;

    /**
     * Set custom hook for processing request
     *
     * @param callable(callable $defaultProcessRequest, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onProcessRequest(callable $callback): self
    {
        $this->crudHookCallbacks['onProcessRequest'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for store validation
     *
     * @param callable(callable $defaultValidation, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onStoreValidation(callable $callback): self
    {
        $this->crudHookCallbacks['onStoreValidation'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for update validation
     *
     * @param callable(callable $defaultValidation, \Illuminate\Http\Request $request, string|int $id): mixed $callback
     */
    protected function onUpdateValidation(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateValidation'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for inline update validation
     *
     * @param callable(callable $defaultValidation, \Illuminate\Http\Request $request, string|int $id): mixed $callback
     */
    protected function onInlineUpdateValidation(callable $callback): self
    {
        $this->crudHookCallbacks['onInlineUpdateValidation'] = $callback;
        return $this;
    }
}
