<?php

namespace Tir\Crud\Support\Hooks;

trait ShowHooks
{
    use BaseHooks;

    /**
     * Set custom hook for complete override of the show process
     *
     * @param callable(callable $defaultShow, string|int $id): mixed $callback
     */
    protected function onShow(callable $callback): self
    {
        $this->crudHookCallbacks['onShow'] = $callback;
        return $this;
    }

    /**
     * Set custom hook for show response
     *
     * @param callable(callable $defaultResponse, mixed $dataModel): mixed $callback
     */
    protected function onShowResponse(callable $callback): self
    {
        $this->crudHookCallbacks['onShowResponse'] = $callback;
        return $this;
    }
}
