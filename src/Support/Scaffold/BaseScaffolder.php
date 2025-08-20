<?php

namespace Tir\Crud\Support\Scaffold;

use Illuminate\Support\Facades\Log;
use Tir\Crud\Support\Scaffold\Actions;
use Tir\Crud\Support\Scaffold\Fields\Button;
use Tir\Crud\Support\Scaffold\FieldsHandler;
use Tir\Crud\Support\Scaffold\Traits\FieldHelper;
use Tir\Crud\Support\Scaffold\Traits\RulesHelper;
use Tir\Crud\Support\Scaffold\Traits\ButtonsHelper;
use Tir\Crud\Support\Scaffold\Traits\ModelIntegration;

abstract class BaseScaffolder
{
    use ButtonsHelper;
    use RulesHelper;
    use FieldHelper;
    use ModelIntegration;

    private string $moduleTitle;
    private string $moduleName;

    private $actions = [];
    private $fieldsHandler = null;

    private $scaffoldedModel;
    private $scaffoldedPage;

    protected abstract function setModuleName(): string;

    protected abstract function setFields(): array;

    protected abstract function setModel(): string;



    public function __construct()
    {
        // Initialize the scaffolder with the model and module name

        $this->moduleName = $this->setModuleName();
        $this->moduleTitle = $this->setModuleTitle();
        $this->actions = $this->setActions();

        $this->currentModel = $this->model();

    }

    protected function setButtons(): array
    {
        return [
            Button::make('back')->display(trans('core::panel.back'))->action('Cancel'),
            Button::make('submit')->display(trans('core::panel.submit'))->action('Submit')->hideFromDetail(),
        ];
    }

    protected function setModuleTitle(): string
    {
        return $this->moduleName;
    }

    /**
     * Configure which actions are available for this resource
     *
     * Override this method to customize available actions using the type-safe ActionType enum.
     *
     * @return array<string, bool> Actions configuration
     *
     * @example return Actions::all();                                    // All actions enabled
     * @example return Actions::basic();                                  // Basic CRUD without soft deletes
     * @example return Actions::readOnly();                               // Only index and show
     * @example return Actions::only(ActionType::INDEX, ActionType::SHOW); // Specific actions only
     * @example return Actions::except(ActionType::DESTROY);              // All except specific actions
     */
    protected function setActions(): array
    {
        // Default: all actions enabled
        return Actions::all();
    }




    public function scaffold($page = '', $model = null): static
    {

        if(isset($this->scaffoldedModel) && isset($this->scaffoldedPage)){
            if($this->scaffoldedModel === $model && $this->scaffoldedPage === $page){
                // If the model and page are the same, return the current instance
                return $this;
            }
        }
        Log::debug('Scaffolder: Re-initializing for model: ' . ($model ? get_class($model) : 'null') . ' and page: ' . $page . ' ' . 'Item ID: ' . ($model ? $model->getKey() : 'null'));
        $this->scaffoldedModel = $model;
        $this->scaffoldedPage = $page;

        $this->fieldsHandler = new FieldsHandler($this->setFields(), $page, $model);
        $this->addButtonsToScaffold();
        return $this;
    }



    // Getters functions:
    public final function getModuleName(): string
    {
        return $this->moduleName;
    }


    private function getConfigs(): array
    {
        $modelClass = $this->model();
        return [
            'actions'      => $this->getActions(),
            'module_title' => $this->moduleTitle,
            'primary_key'  => (new $modelClass)->getKeyName(),
        ];
    }

    final function model(): string
    {
        return $this->setModel();
    }

    final function getActions(): array
    {
        return $this->actions;
    }

    final function moduleName(): string
    {
        return $this->moduleName;
    }

    final function fieldsHandler(): FieldsHandler
    {

        if (!$this->fieldsHandler) {
            throw new \RuntimeException('Fields handler not initialized. Call scaffold() first.');
        }
        return $this->fieldsHandler;
    }

    final function getIndexFields(): array
    {

        if (!$this->fieldsHandler) {
            throw new \RuntimeException('Fields handler not initialized. Call scaffold() first.');
        }

        return $this->fieldsHandler->getIndexFields();
    }

    final function getCreateFields(): array
    {

        if (!$this->fieldsHandler) {
            throw new \RuntimeException('Fields handler not initialized. Call scaffold() first.');
        }

        return $this->fieldsHandler->getCreateFields();
    }

    final function getEditFields(): array
    {

        if (!$this->fieldsHandler) {
            throw new \RuntimeException('Fields handler not initialized. Call scaffold() first.');
        }

        return $this->fieldsHandler->getEditFields();
    }

    final function getDetailFields(): array
    {

        if (!$this->fieldsHandler) {
            throw new \RuntimeException('Fields handler not initialized. Call scaffold() first.');
        }

        return $this->fieldsHandler->getDetailFields();
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
            'fields'        => $this->getDetailFields(),
            'buttons'       => $this->getDetailButtons(),
            'validationMsg' => $this->getValidationMsg(),
            'configs'       => $this->getConfigs()
        ];
    }


}
