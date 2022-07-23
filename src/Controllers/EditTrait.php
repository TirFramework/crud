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

        $dataModel = $this->model->accessLevel()->findOrFail($id);
        $dataModel->scaffold($dataModel);
        return $dataModel->getEditFields();

    }


}
