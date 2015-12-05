<?php namespace Wn\Generators;

use Illuminate\Support\ServiceProvider;

class CommandsServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerModelCommand();
        $this->registerControllerRestActionsCommand();
        $this->registerControllerCommand();
        $this->registerRouteCommand();
        $this->registerMigrationCommand();
        $this->registerResourceCommand();
        $this->registerResourcesCommand();
        $this->registerPivotTableCommand();
        // $this->registerFactoryCommand();
        // $this->registerSeedCommand();
        // $this->registerTestCommand();
    }

    protected function registerModelCommand(){
        $this->app->singleton('command.wn.model', function($app){
            return $app['Wn\Generators\Commands\ModelCommand'];
        });
        $this->commands('command.wn.model');
    }

    protected function registerControllerRestActionsCommand(){
        $this->app->singleton('command.wn.controller.rest-actions', function($app){
            return $app['Wn\Generators\Commands\ControllerRestActionsCommand'];
        });
        $this->commands('command.wn.controller.rest-actions');
    }

    protected function registerControllerCommand(){
        $this->app->singleton('command.wn.controller', function($app){
            return $app['Wn\Generators\Commands\ControllerCommand'];
        });
        $this->commands('command.wn.controller');
    }

    protected function registerMigrationCommand(){
        $this->app->singleton('command.wn.migration', function($app){
            return $app['Wn\Generators\Commands\MigrationCommand'];
        });
        $this->commands('command.wn.migration');
    }

    protected function registerSeedCommand(){
        $this->app->singleton('command.wn.seed', function($app){
            return $app['Wn\Generators\Commands\SeedCommand'];
        });
        $this->commands('command.wn.seed');
    }

    protected function registerRouteCommand(){
        $this->app->singleton('command.wn.route', function($app){
            return $app['Wn\Generators\Commands\RouteCommand'];
        });
        $this->commands('command.wn.route');
    }

    protected function registerTestCommand(){
        $this->app->singleton('command.wn.test', function($app){
            return $app['Wn\Generators\Commands\TestCommand'];
        });
        $this->commands('command.wn.test');
    }

    protected function registerResourceCommand(){
        $this->app->singleton('command.wn.resource', function($app){
            return $app['Wn\Generators\Commands\ResourceCommand'];
        });
        $this->commands('command.wn.resource');
    }

    protected function registerResourcesCommand(){
        $this->app->singleton('command.wn.resources', function($app){
            return $app['Wn\Generators\Commands\ResourcesCommand'];
        });
        $this->commands('command.wn.resources');
    }

    protected function registerPivotTableCommand(){
        $this->app->singleton('command.wn.pivot-table', function($app){
            return $app['Wn\Generators\Commands\PivotTableCommand'];
        });
        $this->commands('command.wn.pivot-table');
    }

    protected function registerFactoryCommand(){
        $this->app->singleton('command.wn.factory', function($app){
            return $app['Wn\Generators\Commands\FactoryCommand'];
        });
        $this->commands('command.wn.factory');
    }

}
