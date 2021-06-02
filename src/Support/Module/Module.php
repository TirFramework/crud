<?php

namespace Tir\Crud\Support\Module;


class Module
{
    public string $name;

    public array $permissions;

    public bool $status;

    private Module $obj;

    public function __construct()
    {
        $this->setDefaultPermissions();
    }

    private function setDefaultPermissions(): void
    {
        $this->permissions = [
            [
                'index' => trans('crud::panel.index'),
                'allow' => trans('crud::panel.allow'),
                'owner' => trans('crud::panel.owner'),
                'deny'  => trans('crud::panel.deny')
            ],

            [
                'create' => trans('crud::panel.create'),
                'allow'  => trans('crud::panel.allow'),
                'deny'   => trans('crud::panel.deny')
            ],


            [
                'edit'  => trans('crud::panel.edit'),
                'allow' => trans('crud::panel.allow'),
                'owner' => trans('crud::panel.owner'),
                'deny'  => trans('crud::panel.deny')
            ],

            [
                'delete' => trans('crud::panel.delete-restore'),
                'allow'  => trans('crud::panel.allow'),
                'owner'  => trans('crud::panel.owner'),
                'deny'   => trans('crud::panel.deny')
            ],
            [
                'fullDelete' => trans('crud::panel.full-delete'),
                'allow'      => trans('crud::panel.allow'),
                'owner'      => trans('crud::panel.owner'),
                'deny'       => trans('crud::panel.deny')
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