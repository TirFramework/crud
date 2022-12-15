<?php

return [

    //This value config which admin panel user in scaffolding system.

    'middlewares'=> env('CRUD_MIDDLEWARE',['api:auth']),
    'localization' => true
];