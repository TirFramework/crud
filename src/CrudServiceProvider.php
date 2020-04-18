<?php

namespace Tir\Crud;


use Tir\Crud\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Tir\Crud\Services\ResourceRegistrar;

class CrudServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //Merge config file in package and published config file.
        $this->mergeConfigFrom(
            __DIR__.'/Config/crud.php', 'crud'
        );

        //Add CustomEnhancement resource routing
        //this route register several route resource those use in CRUD Module
        $registrar = new ResourceRegistrar($this->app['router']);
        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        $this->loadViewsFrom(__DIR__.'/Resources/Views', 'crud');

        $this->loadTranslationsFrom(__DIR__.'/Resources/Lang/', 'crud');

        $this->publishes([
            __DIR__.'/config/crud.php' => config_path('crud.php'),
        ]);
    }
}



// $packages = scandir(base_path().'/vendor/amaj/'); 

//         $models = [];
//         foreach($packages as $package){
            
//             if($package != '.' && $package != '..'){
//                $packageModels =  File::allfiles(base_path().'/vendor/amaj/'.$package.'/src/models');
//                foreach ($packageModels as $model) {
//                    array_push($models, pathinfo($model));
//                    $fileName = pathinfo($model)['basename'];
//                    $path = pathinfo($model)['dirname'].'/';
//                    $filePath = $path.$fileName;
//                    $targetPath=base_path('storage/PackagesModels/');
//                    $targetFilePath = $targetPath.$fileName;

//                    if(!File::isDirectory($targetPath)){
//                        File::makeDirectory($targetPath, 0775, true, true);
//                    }

//                    File::copy($filePath,$targetFilePath);
            
//                 }
//             }
            
//         }
//        $models =  File::allfiles(base_path().'/storage/PackagesModels/');


//         foreach ($models as $model) {
//             $fileName = pathinfo($model)['basename'];
//             $path = pathinfo($model)['dirname'].'/';
//             $filePath = $path.$fileName;

//             $search = 'namespace Amaj\\'.pathinfo($model)['filename'].'\\Models';
//             $replace = 'namespace PackagesModels';

//             file_put_contents($filePath, str_replace($search, $replace, file_get_contents($filePath)));

//         }