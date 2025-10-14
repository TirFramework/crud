<?php

namespace Tir\Crud\Support\Hooks;

trait AccessControlHooks
{
    use BaseHooks;

    /**
     * Hook to provide custom access control logic
     * Return true to allow access, false to deny, null for default behavior
     *
     * @param callable(string $method): bool|null $callback
     *
     * Example:
     * $this->onCheckAccess(function ($method) {
     *     if ($method === 'destroy') {
     *         return auth()->user()->isAdmin(); // Only admins can delete
     *     }
     *     return true; // Allow all other actions
     * });
     */
    protected function onCheckAccess(callable $callback): self
    {
        $this->crudHookCallbacks['onCheckAccess'] = $callback;
        return $this;
    }
}
