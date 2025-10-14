<?php

namespace Tir\Crud\Support\Hooks;

trait StoreHooks
{
    use \Tir\Crud\Support\Hooks\BaseHooks;

    /**
     * Set custom hook for storing model data
     *
     * @param callable(callable $defaultStore, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onStore(callable $callback): self
    {
        $this->crudHookCallbacks['onStore'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for saving model
     *
     * @param callable(callable $defaultSave, mixed $model, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onSaveModel(callable $callback): self
    {
        $this->crudHookCallbacks['onSaveModel'] = $callback;
        return $this;
    }


    /**
     * Set custom hook for filling model for store
     *
     * @param callable(callable $defaultFill, mixed $model, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onFillModelForStore(callable $callback): self
    {
        $this->crudHookCallbacks['onFillModelForStore'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for storing relations
     *
     * @param callable(callable $defaultStoreRelations, \Illuminate\Http\Request $request, mixed $model): mixed $callback
     */
    protected function onStoreRelations(callable $callback): self
    {
        $this->crudHookCallbacks['onStoreRelations'] = $callback;
        return $this;
    }


    /**
     * Set custom hook for after store operation is completed
     *
     * @param callable(callable $defaultCompleted, mixed $model, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onStoreCompleted(callable $callback): self
    {
        $this->crudHookCallbacks['onStoreCompleted'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for store response
     *
     * @param callable(callable $defaultResponse, mixed $model): mixed $callback
     */
    protected function onStoreResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onStoreResponse'] = $callback;
        return $this;
    }
}
