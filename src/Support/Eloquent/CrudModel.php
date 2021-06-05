<?php

namespace Tir\Crud\Support\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class CrudModel extends Model
{

    protected $dates = ['deleted_at'];


    //Scaffolding
    /**
     * @var array|mixed
     */
    private array $indexFields = [];

    protected abstract function setScaffoldName();

    protected abstract function setFields();

    private array $fields = [];
    private string $scaffoldName;


    /**
     * The attribute can on / off localization for this scaffold
     *
     * @var bool
     */
    public bool $localization;

    function __construct()
    {
        parent::__construct();

        $this->scaffoldName = $this->setScaffoldName();


        $this->addFieldsToScaffold();
        $this->setRouteName();
        $this->setLocalization();
    }


    private function addFieldsToScaffold(): void
    {
        foreach ($this->setFields() as $input) {
            array_push($this->fields, $input->get());
        }
    }


    private function setLocalization(): void
    {
        if (!isset($this->localization)) {
            $this->localization = config('crud.localization');
        }

    }

    private function setRouteName(): string
    {
        if (!isset($this->routeName)) {
            return $this->routeName = $this->scaffoldName;
        }
    }


    final function getFields(): array
    {
        return json_decode(json_encode($this->fields), false);
    }

    final function getScaffoldName(): string
    {
        return $this->scaffoldName;
    }

    final function getModel(): string
    {
        return $this->model;
    }

    final function getRouteName(): string
    {
        return $this->routeName;
    }


    final function getLocalization(): string
    {
        return $this->localization ? $this->scaffoldName . '::panel.' : '';
    }


    final function getIndexFields(): array
    {
        return $this->indexFields = Arr::where($this->getFields(), function ($value) {
            return $value->showOnIndex;
        });
    }


}
