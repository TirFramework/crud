<?php

namespace Tir\Crud\Support\Module;


class Module
{
    public string $name;

    public array $permissions;

    public bool $status;


    public function __construct()
    {
        $this->setDefaultPermissions();
    }

    private function setDefaultPermissions(): void
    {
        $this->permissions = [
            [
                'index' => trans('core::panel.index'),
                'allow' => trans('core::panel.allow'),
                'owner' => trans('core::panel.owner'),
                'deny'  => trans('core::panel.deny')
            ],

            [
                'create' => trans('core::panel.create'),
                'allow'  => trans('core::panel.allow'),
                'deny'   => trans('core::panel.deny')
            ],


            [
                'edit'  => trans('core::panel.edit'),
                'allow' => trans('core::panel.allow'),
                'owner' => trans('core::panel.owner'),
                'deny'  => trans('core::panel.deny')
            ],

            [
                'destroy' => trans('core::panel.delete-restore'),
                'allow'   => trans('core::panel.allow'),
                'owner'   => trans('core::panel.owner'),
                'deny'    => trans('core::panel.deny')
            ],
            [
                'fullDestroy' => trans('core::panel.full-delete'),
                'allow'       => trans('core::panel.allow'),
                'owner'       => trans('core::panel.owner'),
                'deny'        => trans('core::panel.deny')
            ]
        ];
    }


    public function setName(string $name): Module
    {
        $this->name = $name;

        return $this;
    }

    public function enable(): Module
    {
        $this->status = true;

        return $this;
    }

    public function disable(): Module
    {
        $this->status = false;

        return $this;
    }

    public function status(): bool
    {
        return $this->status;
    }


    public function get(): array
    {
        return (array)$this;
    }

}
