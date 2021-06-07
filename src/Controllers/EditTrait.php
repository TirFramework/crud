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
        $item = $this->model->findOrFail($id);
        return View::first([$item->moduleName . "::admin.edit", "core::scaffold.edit"])->with(['model' => $item]);
    }


}
