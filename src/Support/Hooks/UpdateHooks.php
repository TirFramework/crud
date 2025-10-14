<?php

namespace Tir\Crud\Support\Hooks;

trait UpdateHooks
{
    use \Tir\Crud\Support\Hooks\BaseHooks;

    /**
     * Set custom hook for updating model data
     *
     * @param callable(callable $defaultUpdate, \Illuminate\Http\Request $request, string|int $id): mixed $callback
     */
    protected function onUpdate(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdate'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for getting model for update
     *
     * @param callable(callable $defaultGet, \Illuminate\Http\Request $request, string|int $id): mixed $callback
     */
    protected function onGetModelForUpdate(callable $callback): self
    {
        $this->crudHookCallbacks['onGetModelForUpdate'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for filling model for update
     *
     * @param callable(callable $defaultFill, mixed $model, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onFillModelForUpdate(callable $callback): self
    {
        $this->crudHookCallbacks['onFillModelForUpdate'] = $callback;
        return $this;
    }


    /**
     * Set custom hook for updating model
     *
     * @param callable(callable $defaultUpdate, mixed $model, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onUpdateModel(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateModel'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for updating relations
     *
     * @param callable(callable $defaultUpdateRelations, \Illuminate\Http\Request $request, mixed $item): mixed $callback
     */
    protected function onUpdateRelations(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateRelations'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for updating a specific relation
     *
     * @param callable(callable $defaultUpdateRelation, mixed $data, string $fieldName, mixed $item, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onUpdateRelation(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateRelation'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for after update operation is completed
     *
     * @param callable(callable $defaultCompleted, mixed $item, \Illuminate\Http\Request $request): mixed $callback
     */
    protected function onUpdateCompleted(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateCompleted'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for update response
     *
     * @param callable(callable $defaultResponse, mixed $item): mixed $callback
     */
    protected function onUpdateResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onUpdateResponse'] = $callback;
        return $this;
    }
}
