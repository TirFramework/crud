<?php

namespace Tir\Crud\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

trait CreateTrait
{

    public function create()
    {
        $fields = $this->model()->getCreateElements();
        return Response::json($fields, '200');
    }


}
