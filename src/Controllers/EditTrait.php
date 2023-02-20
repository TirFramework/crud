<?php

namespace Tir\Crud\Controllers;

trait EditTrait
{
    public function edit(int|string $id)
    {
        $dataModel = $this->model()->findOrFail($id);
        return $dataModel->scaffold($dataModel)->getEditScaffold();
    }

}
