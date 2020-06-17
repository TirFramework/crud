<?php

namespace Tir\Crud;


use Illuminate\Database\Query\Builder;
use Tir\Crud\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Tir\Crud\Services\AdminFileds;
use Tir\Crud\Services\ResourceRegistrar;
use Tir\Setting\Facades\Stg;

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

        $this->registerResetOrderMacro();

        $this->publishes([
            __DIR__.'/config/crud.php' => config_path('crud.php'),
        ]);


    }

    private function registerResetOrderMacro()
    {
        Builder::macro('resetOrders', function () {
            $this->{$this->unions ? 'unionOrders' : 'orders'} = null;
            return $this;
        });
    }


}


