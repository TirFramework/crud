<?php

namespace Tir\Crud;


use Illuminate\Support\ServiceProvider;
use Tir\Crud\Services\Crud;
use Tir\Crud\Support\Resource\ResourceRegistrar;

class CrudServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Config/crud.php', 'crud'
        );

        $this->registerNewRouteResource();

        $this->registerCrudSingleton();
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


    /**
     * Register several route resource those use in CRUD Module
     *
     * @return void
     */
    private function registerNewRouteResource()
    {
        $registrar = new ResourceRegistrar($this->app['router']);
        $this->app->bind('Illuminate\Routing\ResourceRegistrar', function () use ($registrar) {
            return $registrar;
        });
    }


    /**
     * Register a singleton container
     */
    private function registerCrudSingleton()
    {
//        $this->app->singleton('Crud', function (){
//            return new Crud;
//        });

        $crud = \Tir\Crud\Support\Scaffold\Crud::get();
        $crud->setName('test');
    }


}


