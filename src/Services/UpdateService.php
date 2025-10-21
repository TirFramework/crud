<?php

namespace Tir\Crud\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tir\Crud\Support\Hooks\UpdateHooks;
use Tir\Crud\Support\Database\DatabaseAdapterFactory;

class UpdateService
{
    use UpdateHooks;

    private $scaffolder;
    private $model;

    public function __construct($scaffolder)
    {
        $this->scaffolder = $scaffolder;
        $this->model = $scaffolder->modelClass();
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

    public function update($request, $id)
    {
        $model = $this->model;
        $item = $this->getModelById($id, $request, $model );

        $item = $this->updateTransaction($request, $item);
        return $item;

    }



    private function getModelById(int|string $id, $request, $model)
    {
        // Find the item by ID
        $defaultItem = function ($m = null, $i = null, $r = null) use ($model, $id, $request) {
            // If the model is not found, it will throw a ModelNotFoundException
            if ($m !== null) {
                $model = $m;
            }
            if ($i !== null) {
                $id = $i;
            }
            if ($r !== null) {
                $request = $r;
            }
            $model = new $model;
            return $model->findOrFail($id);
        };

        return $this->executeWithHook('onGetModelForUpdate', $defaultItem, $model, $id, $request);
    }


    private function updateTransaction($request, $item): mixed
    {
        return DB::transaction(function () use ($request, $item) { // Start the transaction
            $item = $this->updateModel($request, $item);
            DB::commit();
            return $item;
        });
    }

    private function updateModel($request, $item): mixed
    {
        // Use database adapter for database-specific fillable handling
        $adapter = DatabaseAdapterFactory::create($item->getConnection());

        // Get scaffolder fields for filtering
        $scaffolderFields = $this->scaffolder()->fieldsHandler()->getAllDataFields();


        // Process fillable data using database-specific logic
        $filteredData = $adapter->processFillableData($request->all(), $scaffolderFields, $item);

        // Let each adapter handle its own fillModel logic with scaffolder field context
        $item = $adapter->fillModel($item, $filteredData, $scaffolderFields);

        // Save the model
        $item = $this->saveForUpdate($request, $item);

        $this->updateRelations($request, $item);

        // Define the default behavior for update completed as a closure
        $defaultUpdateCompleted = function ($mdl = null, $req = null) use ($item, $request) {
            if ($mdl !== null) {
                $item = $mdl;
            }
            if ($req !== null) {
                $request = $req;
            }
            return $item;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onUpdateCompleted', $defaultUpdateCompleted, $item, $request);
    }

    private function fillForUpdate($request, $model): mixed
    {
        // Define the default behavior for filling model as a closure
        $defaultFillModel = function ($m = null, $r = null) use ($request, $model) {
            if ($m !== null) {
                $model = $m;
            }
            if ($r !== null) {
                $request = $r;
            }

            return $model->fill($request->all());
        };


        // Pass the closure to the hook
        return $this->executeWithHook('onFillModelForUpdate', $defaultFillModel, $model, $request);
    }

    private function saveForUpdate($request, $model): mixed
    {
        // Define the default behavior for saving model as a closure
        $defaultSaveModel = function ($m = null, $r = null) use ($model, $request) {
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
        return $this->executeWithHook('onUpdateModel', $defaultSaveModel, $model, $request);
    }

    private function updateRelations(Request $request, $item): mixed
    {
        // Define the default behavior for updating relations as a closure
        $defaultUpdateRelations = function ($req = null, $mdl = null) use ($request, $item) {
            if ($req !== null) {
                $request = $req;
            }
            if ($mdl !== null) {
                $item = $mdl;
            }

            foreach ($this->scaffolder()->fieldsHandler()->getAllDataFields() as $field) {
                if (isset($field->relation)) {

                    $data = $request->input($field->name);
                    if (isset($data)) {
                        // Define the default behavior for updating a specific relation
                        $defaultUpdateRelation = function ($d = null, $fld = null, $itm = null, $req = null) use ($data, $field, $item, $request) {
                            if ($d !== null) {
                                $data = $d;
                            }
                            if ($itm !== null) {
                                $item = $itm;
                            }
                            if ($fld !== null) {
                                $field = $fld;
                            }
                            if ($req !== null) {
                                $request = $req;
                            }
                            // Check the relation type and handle accordingly
                            $relation = $item->{$field->relation->name}();
                            $relationType = class_basename($relation);
                            if ($relationType === 'BelongsToMany') {
                                // For BelongsToMany relations, use sync
                                $relation->sync($data);
                            } elseif ($relationType === 'BelongsTo') {
                                // For BelongsTo relations, associate the related model
                                if ($data) {
                                    $relation->associate($data);
                                } else {
                                    $relation->dissociate();
                                }
                                $item->save();
                            }elseif($relationType === 'HasMany'){
                                // For HasMany relations, we can use saveMany or similar methods
                                // Here we assume $data is an array of related model IDs
                                $relatedModel = $relation->getRelated();
                                $relatedInstances = $relatedModel->whereIn('id', $data)->get();
                                $relation->saveMany($relatedInstances);
                            }
                            return $item;
                        };

                        // Pass the closure to the hook
                        $this->executeWithHook('onUpdateRelation', $defaultUpdateRelation, $data, $field, $item, $request);
                    }
                }
            }

            return $item;
        };

        // Pass the closure to the hook
        return $this->executeWithHook('onUpdateRelations', $defaultUpdateRelations, $request, $item);
    }
}
