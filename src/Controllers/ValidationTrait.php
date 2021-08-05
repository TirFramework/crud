<?php


namespace Tir\Crud\Controllers;


use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Route;

trait ValidationTrait
{

    public function __construct()
    {
        parent::__cunstruct();
        $this->validation();

    }


    private function validation()
    {
        if (Request::method() == 'POST') {
            $this->storeValidation();
        }

        if (Request::method() == 'PUT') {
            $this->updateValidation();
        }
    }

    public function storeValidation()
    {
        $validator = Validator::make(Request::all(), $this->model->getCreationRules());
        if ($validator->fails()) {
            abort(Response::Json([
                'error'   => 'validation_error',
                'message' => $validator->errors(),
            ], 422));
        }

    }


    public function updateValidation()
    {
        //get route(url) parameter {id}
        $id = request()->route()->parameter($this->model->getModuleName());

        $model = $this->model->findOrFail($id);
        $model->scaffold();
        $validator = Validator::make(Request::all(), $model->getUpdateRules());

        if ($validator->fails()) {
            abort(Response::Json([
                'error'   => 'validation_error',
                'message' => $validator->errors(),
            ], 422));
        }


    }
}

