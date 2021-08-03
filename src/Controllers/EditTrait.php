<?php

namespace Tir\Crud\Controllers;

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
        return $model->getEditFields();

    }


}
