<?php namespace Wn\Generators;

use Illuminate\Support\ServiceProvider;

class CommandsServiceProvider extends ServiceProvider
{

    public function register()
    {
        // $this->register
        $this->registerModelCommand();
        // $this->registerControllerCommand();
        // $this->registerMigrationCommand();
        // $this->registerSeedCommand();
        // $this->registerRouteCommand();
        // $this->registerTestCommand();
        // $this->registerResourceCommand();
    }

    protected function registerModelCommand(){
        $this->app->singleton('command.wn.model', function($app){
            return $app['Wn\Generators\Commands\ModelCommand'];
        });
        $this->commands('command.wn.model');

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

}
