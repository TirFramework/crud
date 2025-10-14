<?php

namespace Tir\Crud\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tir\Crud\Support\Hooks\StoreHooks;
use Tir\Crud\Support\Database\DatabaseAdapterFactory;

class StoreService
{
    use StoreHooks;

    private $scaffolder;
    private $model;

    public function __construct($scaffolder, $model)
    {
        $this->scaffolder = $scaffolder;
        $this->model = $model;
    }

    /**
     * Set hooks from controller
     */
    public function setHooks(array $hooks): void
    {
        $this->crudHookCallbacks = $hooks;
    }

    private function scaffolder()
    {
        return $this->scaffolder;
    }

    private function model()
    {
        return $this->model;
    }

    public function store($request)
    {
        return $this->storeTransaction($request);
    }

    private function storeTransaction($request)
    {
        return DB::transaction(function () use ($request) {
            $model = $this->storeModel($request);
            DB::commit();
            return $model;
        });
    }

    /**
     * This function store crud and relations
     */
    private function storeModel($request)
    {
        // Use database adapter for database-specific fillable handling
        $model = $this->model();
        $adapter = DatabaseAdapterFactory::create($model->getConnection());

        // Get scaffolder fields for filtering
        $scaffolderFields = $this->scaffolder()->fieldsHandler()->getAllDataFields();

        // Process fillable data using database-specific logic
        $filteredData = $adapter->processFillableData($request->all(), $scaffolderFields, $model);

        // Let each adapter handle its own fillModel logic with scaffolder field context
        $model = $adapter->fillModel($model, $filteredData, $scaffolderFields);

        // Save the model
        $model = $this->save($request, $model);

        // Store relations
        $this->storeRelations($request, $model);

        // Define the default behavior for store completed as a closure
        $defaultStoreCompleted = function($m = null, $r = null) use ($model, $request) {
            if ($m !== null) {
                $model = $m;
            }
            if ($r !== null) {
                $request = $r;
            }
            return $model;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onStoreCompleted', $defaultStoreCompleted, $model, $request);
    }

    private function storeRelations($request, $model): void
    {
        // Define the default behavior for updating relations as a closure
        $defaultStoreRelations = function($req = null, $mdl = null) use ($request, $model) {
            if ($req !== null) {
                $request = $req;
            }
            if ($mdl !== null) {
                $model = $mdl;
            }

            foreach ($this->scaffolder()->fieldsHandler()->getAllDataFields() as $field) {
                if (isset($field->relation) && $field->multiple) {
                    $data = $request->input($field->name);
                    if (isset($data)) {
                        // Define the default behavior for updating a specific relation
                        $defaultStoreRelation = function($d = null, $fieldName = null, $mdl = null, $req = null) use ($data, $field, $model, $request) {
                            if ($d !== null) {
                                $data = $d;
                            }
                            if ($mdl !== null) {
                                $model = $mdl;
                            }
                            if ($req !== null) {
                                $request = $req;
                            }
                            $model->{$field->relation->name}()->sync($data);
                            return $data;
                        };

                        // Pass the closure to the hook
                        $this->executeWithHook('onStoreRelation', $defaultStoreRelation, $data, $field->name, $model, $request);
                    }
                }
            }

            return $model;
        };

        // Pass the closure to the hook
        $this->executeWithHook('onStoreRelations', $defaultStoreRelations, $request, $model);
    }

    private function fill($request, $model)
    {
        // Define the default behavior for filling model as a closure
        $defaultFillModel = function($m = null, $r = null) use ($request, $model) {
            if ($m !== null) {
                $model = $m;
            }
            if ($r !== null) {
                $request = $r;
            }

            return $model->fill($request->all());
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onFillModelForStore', $defaultFillModel, $model, $request);
    }

    private function save($request, $model)
    {
        // Define the default behavior for saving model as a closure
        $defaultSaveModel = function($m = null, $r = null) use ($model, $request) {
            if ($m !== null) {
                $model = $m;
            }
            if ($r !== null) {
                $request = $r;
            }
            $model->save();
            return $model;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onSaveModel', $defaultSaveModel, $model, $request);
    }
}
