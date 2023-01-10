<?php

namespace Tir\Crud\Support\Scaffold;

use Tir\Crud\Support\Scaffold\Fields\Button;

trait BaseScaffold
{
    use FieldsHelper;
    use ButtonsHelper;
    use RulesHelper;

    private string $moduleTitle;
    public bool $isScaffolded = false;
    protected bool $accessLevelControl = true;
    private string $moduleName;
    private array $fields = [];
    private array $buttons = [];
    private array $actionsStatus = [
        'index'   => true,
        'create'  => true,
        'edit'    => true,
        'destroy' => true,
        'show'    => true
    ];


    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

    protected function setButtons(): array
    {
        return [];
    }

    protected function setModuleTitle(): string
    {
        return $this->moduleName;
    }

    public function scaffold($dataModel = null): static
    {
        if ($this->isScaffolded) {
            dd('You cannot make scaffold again');
        }
        $this->isScaffolded = true;
        $this->moduleName = $this->setModuleName();
        $this->moduleTitle = $this->setModuleTitle();
        $this->addFieldsToScaffold($dataModel);
        $this->addButtonsToScaffold();
        return $this;
    }

    private function addFieldsToScaffold($dataModel): void
    {
        foreach ($this->setFields() as $field) {
            $this->fields[] = $field->get($dataModel);
        }
    }

    public function getAccessLevelStatus(): bool
    {
        return $this->accessLevelControl;
    }

    private function getConfigs(): array
    {
        return [
            'module_title' => $this->moduleTitle
        ];
    }

    final function setActionsStatus($action, $status): bool
    {
        $this->actionsStatus[$action] = $status;
    }

    final function getActionsStatus(): array
    {
        return $this->actionsStatus;
    }

    final function getModuleName(): string
    {
        return $this->setModuleName();
    }

    final function getModel(): string
    {
        return $this->model;
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }

    final function getIndexScaffold(): array
    {
        return [
            'fields'  => $this->getIndexFields(),
            'buttons' => $this->getIndexButtons(),
            'configs' => $this->getConfigs()
        ];
    }

    final function getCreateScaffold(): array
    {
        return [
            'fields'        => $this->getCreateFields(),
            'buttons'       => $this->getCreateButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }

    final function getEditScaffold(): array
    {
        return [
            'fields'        => $this->getEditFields(),
            'buttons'       => $this->getEditButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }

    final function getDetailScaffold(): array
    {
        return [
            'fields'  => $this->getDetailFields(),
            'buttons' => $this->getDetailButtons(),
            'config'  => []
        ];
    }

}
