<?php

return [

    //This value config which admin panel user in scaffolding system.

    'middlewares'=> env('CRUD_MIDDLEWARES',['auth:sanctum']),

    'accessLevelControl' => env('CRUD_ACCESS_LEVEL_CONTROL','on'),

    'access_class' => \Tir\Crud\Support\Acl\Access::Class,


];
