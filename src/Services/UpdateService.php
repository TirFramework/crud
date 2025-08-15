<?php

namespace Tir\Crud\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tir\Crud\Support\Hooks\UpdateHooks;

class UpdateService
{
    use UpdateHooks;

    private $scaffolder;
    private $model;

    public function __construct($scaffolder)
    {
        $this->scaffolder = $scaffolder;
        $this->model = $scaffolder->model();
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
            $item = $this->getModelById($id, $request, $this->model());

            $item = $this->updateTransaction($request, $item);
            return $item;

     }



    private function getModelById(int|string $id, $request, $model)
    {
            // Find the item by ID
            $defaultItem = function($m = null, $i = null) use ($model, $id) {
                // If the model is not found, it will throw a ModelNotFoundException
                if( $m !== null) {
                    $model = $m;
                }
                if( $i !== null) {
                    $id = $i;
                }
                $model = new $model;
                return $model->findOrFail($id);
            };

            $customItem = $this->callHook('onGetModelForUpdate', $defaultItem, $request, $id);
            if ($customItem !== null) {
                return $customItem;
            }

            return $defaultItem();
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
        // Store model
        $modelFillable = $item->getFillable();
        $modelGuarded = $item->getGuarded();

        // Fillable columns from scaffolder and model
        $item->fillable($this->scaffolder()->fieldsHandler()->getFillableColumns($modelFillable, $modelGuarded));

        // Fill the model with request data
        $item = $this->fillForUpdate($request, $item);

        // Save the model
        $item = $this->saveForUpdate($request, $item);

        $this->updateRelations($request, $item);

        // Define the default behavior for update completed as a closure
        $defaultUpdateCompleted = function($mdl = null, $req = null) use ($item, $request) {
            if ($mdl !== null) {
                $item = $mdl;
            }
            if ($req !== null) {
                $request = $req;
            }
            return $item;
        };

        // Pass the closure to the hook
        $customUpdateCompleted = $this->callHook('onUpdateCompleted', $defaultUpdateCompleted, $item, $request);
        if($customUpdateCompleted !== null) {
            return $customUpdateCompleted;
        }

        // Otherwise, return the result directly
        return $defaultUpdateCompleted();
    }

    private function fillForUpdate($request, $model): mixed
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
        $customFillModel = $this->callHook('onFillModelForUpdate', $defaultFillModel, $model, $request);
        if($customFillModel !== null) {
            return $customFillModel;
        }

        // Otherwise, return the result directly
        return $defaultFillModel();
    }

    private function saveForUpdate($request, $model): mixed
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
        $customSaveModel = $this->callHook('onUpdating', $defaultSaveModel, $model, $request);
        if($customSaveModel !== null) {
            return $customSaveModel;
        }

        // Otherwise, return the result directly
        return $defaultSaveModel();
    }

    private function updateRelations(Request $request, $item): mixed
    {
        // Define the default behavior for updating relations as a closure
        $defaultUpdateRelations = function($req = null, $mdl = null) use ($request, $item) {
            if ($req !== null) {
                $request = $req;
            }
            if ($mdl !== null) {
                $item = $mdl;
            }

            foreach ($this->scaffolder()->fieldsHandler()->getAllDataFields() as $field) {
                if (isset($field->relation) && $field->multiple) {

                    $data = $request->input($field->name);
                    if (isset($data)) {
                        // Define the default behavior for updating a specific relation
                        $defaultUpdateRelation = function($d = null, $itm = null, $req = null) use ($data, $field, $item, $request) {
                            if ($d !== null) {
                                $data = $d;
                            }
                            if ($itm !== null) {
                                $item = $itm;
                            }
                            if ($req !== null) {
                                $request = $req;
                            }
                            $item->{$field->relation->name}()->sync($data);
                        };

                        // Pass the closure to the hook
                        $customUpdateRelation = $this->callHook('onUpdateRelation', $defaultUpdateRelation, $data, $field->name, $item, $request);
                        if($customUpdateRelation === null) {
                            $defaultUpdateRelation();
                        }
                    }
                }
            }

            return $item;
        };

        // Pass the closure to the hook
        $customUpdateRelations = $this->callHook('onUpdateRelations', $defaultUpdateRelations, $request, $item);
        if($customUpdateRelations === null) {
            return $defaultUpdateRelations();
        }
        return $customUpdateRelations;
    }
}
