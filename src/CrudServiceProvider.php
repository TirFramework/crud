<?php

namespace Tir\Crud;


use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Tir\Crud\Components\Field;
use Tir\Crud\Support\Module\AdminMenu;
use Tir\Crud\Support\Module\Modules;
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
            __DIR__ . '/Config/crud.php', 'crud'
        );
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Lang/', 'core');


        $this->registerNewRouteResource();

        $this->registerModulesSingleton();

        $this->adminMenu();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/Resources/Views', 'core');
        $this->publishes([
            __DIR__ . '/config/crud.php' => config_path('crud.php'),
        ]);

        Blade::component('field', Field::class);


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

    private function registerModulesSingleton()
    {
        Modules::init();
    }

    private function adminMenu()
    {
        $this->app->singleton('AdminMenu', function ($app) {
            return new AdminMenu;
        });
    }
}
