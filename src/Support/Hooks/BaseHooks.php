<?php

namespace Tir\Crud\Support\Hooks;

trait BaseHooks
{
    /**
     * The hook callbacks storage
     *
     * @var array
     */
    protected array $crudHookCallbacks = [];

    /**
     * Initialize hooks from a CrudHooks object
     */
    // protected function crudHooks(CrudHooks $hooks): void
    // {
    //     $this->crudHookCallbacks = $hooks->getHooks();
    // }

    /**
     * Check if a hook callback exists and call it
     */
    // protected function callHookIfExists(string $hookName, ...$args)
    // {

    //     if (isset($this->crudHookCallbacks[$hookName])) {

    //         call_user_func_array($this->crudHookCallbacks[$hookName], $args);
    //     }

    //     return $args[0] ?? null; // Return first argument if no hook exists
    // }

    protected function callHook(string $hookName, ...$args)
    {
        if (isset($this->crudHookCallbacks[$hookName])) {
            return call_user_func_array($this->crudHookCallbacks[$hookName], $args);
        }
        return null; // Return first argument if no hook exists
    }

    /**
     * Check if a specific hook exists and is callable
     */
    protected function hasHook(string $hookName): bool
    {
        return isset($this->crudHookCallbacks[$hookName]) &&
               is_callable($this->crudHookCallbacks[$hookName]);
    }

    /**
     * Execute with hook pattern - run hook if exists, otherwise run default callback
     *
     * @param string $hookName The name of the hook to check
     * @param callable $defaultCallback The default behavior to execute if no hook exists
     * @param mixed ...$args Arguments to pass to hook or default callback
     * @return mixed Result from hook or default callback
     */
    protected function executeWithHook(string $hookName, callable $defaultCallback, ...$args)
    {
        if ($this->hasHook($hookName)) {
            return $this->callHook($hookName, $defaultCallback, ...$args);
        }

        return call_user_func($defaultCallback, ...$args);
    }
}
