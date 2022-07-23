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
                'page' => [
                    'label' => trans('core::panel.index'),
                    'value' => 'index'
                ],
                'access' => [
                    [
                        'label' => trans('core::panel.allow'),
                        'value' => 'allow',
                    ],
                    [
                        'label' => trans('core::panel.owner'),
                        'value' => 'owner',
                    ],
                    [
                        'label' => trans('core::panel.deny'),
                        'value' => 'deny',
                    ]
                ]
            ],
            [
                'page' => [
                    'label' => trans('core::panel.create'),
                    'value' => 'create'
                ],
                'access' => [
                    [
                        'label' => trans('core::panel.allow'),
                        'value' => 'allow',
                    ],
                    [
                        'label' => trans('core::panel.deny'),
                        'value' => 'deny',
                    ]
                ]
            ],
            [
                'page' => [
                    'label' => trans('core::panel.edit'),
                    'value' => 'edit'
                ],
                'access' => [
                    [
                        'label' => trans('core::panel.allow'),
                        'value' => 'allow',
                    ],
                    [
                        'label' => trans('core::panel.owner'),
                        'value' => 'owner',
                    ],
                    [
                        'label' => trans('core::panel.deny'),
                        'value' => 'deny',
                    ]
                ]
            ],
            [
                'page' => [
                    'label' => trans('core::panel.delete-restore'),
                    'value' => 'delete'
                ],
                'access' => [
                    [
                        'label' => trans('core::panel.allow'),
                        'value' => 'allow',
                    ],
                    [
                        'label' => trans('core::panel.owner'),
                        'value' => 'owner',
                    ],
                    [
                        'label' => trans('core::panel.deny'),
                        'value' => 'deny',
                    ]
                ]
            ],
            [
                'page' => [
                    'label' => trans('core::panel.full-delete'),
                    'value' => 'full-delete'
                ],
                'access' => [
                    [
                        'label' => trans('core::panel.allow'),
                        'value' => 'allow',
                    ],
                    [
                        'label' => trans('core::panel.owner'),
                        'value' => 'owner',
                    ],
                    [
                        'label' => trans('core::panel.deny'),
                        'value' => 'deny',
                    ]
                ]
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
