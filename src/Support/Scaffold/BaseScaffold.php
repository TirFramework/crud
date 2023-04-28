<?php

namespace Tir\Crud\Support\Scaffold;

use App\InferaStructures\Acl\Access;
use Tir\Crud\Support\Scaffold\Fields\Button;

trait BaseScaffold
{
    use FieldsHelper;
    use ButtonsHelper;
    use RulesHelper;

    private string $moduleTitle;
    public bool $isScaffolded = false;
    private string $moduleName;
    private array $fields = [];
    private array $buttons = [];
    private array $actions = [];


    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

    protected function setButtons(): array
    {
        return [
            Button::make('back')->action('Cancel'),
            Button::make('submit')->display('panel.submit')->action('Submit')->hideFromDetail(),
        ];
    }

    protected function scaffoldBoot(): void
    {
        //
    }

    protected function setAcl(): bool
    {
        return true;
    }


    protected function setModuleTitle(): string
    {
        return $this->moduleName;
    }


    protected function setActions(): array
    {
        return [];
    }

    private function scaffold($page=''): static
    {
        if ($this->isScaffolded) {
            return $this;
        }
        $this->scaffoldBoot();
        $this->moduleName = $this->setModuleName();
        $this->moduleTitle = $this->setModuleTitle();
        $this->actions = $this->setActions();
        $this->initActions();
        $this->addFieldsToScaffold($page);
        $this->addButtonsToScaffold();
        $this->isScaffolded = true;
        return $this;
    }

    private function addFieldsToScaffold($page): void
    {
        foreach ($this->setFields() as $field) {
            $field->page($page);
            if($page === 'detail'){
                $field->readonly();
            }
            //here $this is the Model with data
            $this->fields[] = $field->get($this);
        }
    }

    public function getAccessLevelStatus(): bool
    {
        return $this->setAcl();
    }

    private function getConfigs(): array
    {
        return [
            'actions'     => $this->getActions(),
            'module_title' => $this->moduleTitle
        ];
    }

    private function initActions(): void
    {
        $baseActions = [
            'index'   => Access::check($this->moduleName, 'index')!== 'deny',
            'create'  => Access::check($this->moduleName, 'create')!== 'deny',
            'show'    => Access::check($this->moduleName, 'show')!== 'deny',
            'edit'    => Access::check($this->moduleName, 'edit')!== 'deny',
            'destroy' => Access::check($this->moduleName, 'destroy')!== 'deny',
            'fullDestroy' => Access::check($this->moduleName, 'fullDestroy')!== 'deny',
        ];
         $this->actions = array_merge($this->actions, $baseActions);

    }


    final function getActions(): array
    {
        return $this->actions;
    }

    final function getModuleName(): string
    {
        if(isset($this->moduleName)){
            return $this->moduleName;
        }
        return $this->setModuleName();
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }

    final function getIndexScaffold(): array
    {
        $this->scaffold('index');
        return [
            'fields'  => $this->getIndexFields(),
            'buttons' => $this->getIndexButtons(),
            'configs' => $this->getConfigs()
        ];
    }

    final function getCreateScaffold(): array
    {
        $this->scaffold('create');
        return [
            'fields'        => $this->getCreateFields(),
            'buttons'       => $this->getCreateButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }

    final function getEditScaffold(): array
    {
        $this->scaffold('edit');

        return [
            'fields'        => $this->getEditFields(),
            'buttons'       => $this->getEditButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }

    final function getDetailScaffold(): array
    {
        $this->scaffold('detail');
        return [
            'fields'  => $this->getDetailFields(),
            'buttons' => $this->getDetailButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }

}
