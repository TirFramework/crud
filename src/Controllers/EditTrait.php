<?php

namespace Tir\Crud\Controllers;

use Illuminate\Support\Facades\View;
use Tir\Crud\Events\EditEvent;

trait EditTrait
{
    /**
     * This function called from route. run an event and run edit functions
     *
     * @param int $id
     */
    public function edit($id)
    {
        $model = $this->model->findOrFail($id);
        $model->scaffold();
        return View::first([$this->model->moduleName . "::admin.edit", "core::scaffold.edit"])->with(['model' => $model]);
    }


}
