<?php

namespace Tir\Crud\Controllers\Traits;

use Tir\Crud\Support\Hooks\AccessControlHooks;

trait AccessControl
{
    use AccessControlHooks;

    /**
     * Disable access control for this controller
     * Set to false to disable all access checks
     */
    protected bool $accessControlEnabled = true;

    /**
     * Check if user has access to the given method/action
     * Simple and clear - true = allow, false = deny
     */
    private function checkAccess(string $method): bool
    {
        // Global config override
        if (config('crud.accessLevelControl') === 'off') {
            return true; // Allow all access
        }

        // Controller-level property override
        if (!$this->accessControlEnabled) {
            return true; // Allow all access
        }

        // Custom hook check - allows complete control over access
        $defaultAccessCheck = function ($m = null) use ($method) {
            if ($m !== null) {
                $method = $m;
            }

            // Default system access check
            try {
                $accessClass = config('crud.access_class', \Tir\Crud\Support\Acl\Access::class);
                $accessInstance = new $accessClass();
                $module = $this->getModuleName();
                return $accessInstance->checkAccess($module, $method);
            } catch (\Exception $e) {
                return false; // Deny access on error
            }
        };

        return (bool) $this->executeWithHook('onCheckAccess', $defaultAccessCheck, $method);
    }

    /**
     * Perform access check and abort if denied
     */
    private function enforceAccess(string $method): void
    {
        if (!$this->checkAccess($method)) {
            abort(403, 'Access denied to ' . $method . ' action');
        }
    }

    /**
     * Get module name for access control
     */
    private function getModuleName(): string
    {
        return $this->scaffolder()->getModuleName();
    }

    /**
     * Get available actions filtered by user access
     * Used when sending actions to frontend
     */
    protected function getAvailableActions(): array
    {
        $businessActions = $this->scaffolder()->getActions();
        $filtered = [];

        foreach ($businessActions as $action => $enabled) {
            if ($enabled) {
                $filtered[$action] = $this->checkAccess($action);
            } else {
                $filtered[$action] = false;
            }
        }

        return $filtered;
    }


    private function checkActionEnabled(string $method): void
    {
        $actionsClass = \Tir\Crud\Support\Scaffold\Actions::class;

        // Check if action is directly enabled
        if ($actionsClass::isEnabled($this->scaffolder->getActions(), $method)) {
            return;
        }

        // Check if mapped base action is enabled
        $mappedAction = $this->mapCrudAction($method);
        if ($mappedAction !== $method && $actionsClass::isEnabled($this->scaffolder->getActions(), $mappedAction)) {
            return;
        }

        // Define all default actions and their variants
        $defaultActions = [
            'index', 'create', 'show', 'edit', 'destroy', 'forceDelete', 'restore', 'inlineEdit',
            'data', 'select', 'store', 'update' // route variants
        ];

        // If it's a default action/variant and neither direct nor mapped action is enabled, block it
        if (in_array($method, $defaultActions)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(
                "Action '{$method}' is not enabled for this resource."
            );
        }

        // Custom actions allowed by default
    }    /**
     * Map route action names to their base CRUD actions
     */
    private function mapCrudAction(string $action): string
    {
        $baseActions = [
            'data' => 'index',
            'select' => 'index',
            'store' => 'create',
            'update' => 'edit',
            'restore' => 'destroy',
            'forceDelete' => 'forceDelete',
        ];

        return $baseActions[$action] ?? $action;
    }
}
