<?php namespace Wn\Generators\Commands;


use InvalidArgumentException;

class RouteCommand extends BaseCommand {

	protected $signature = 'wn:route
		{resource : Name of the resource.}
        {--controller= : Name of the RESTful controller.}
        {--laravel= : Boolean (default false) Use Laravel style route definitions
    ';

	protected $description = 'Generates RESTful routes.';

    public function handle()
    {
        $resource = $this->argument('resource');
        $laravelRoutes = $this->input->hasOption('laravel') ? $this->option('laravel') : false;

        $templateFile = 'routes';
        $routesPath = 'routes/web.php';
        if ($laravelRoutes) {
            $templateFile = 'routes-laravel';
            $routesPath = 'routes/api.php';
            if (!$this->fs->isFile($routesPath)) {
                $this->fs->put($routesPath, "
                <?php

                    use Illuminate\Http\Request;
                    
                    /*
                    |--------------------------------------------------------------------------
                    | API Routes
                    |--------------------------------------------------------------------------
                    |
                    | Here is where you can register API routes for your application. These
                    | routes are loaded by the RouteServiceProvider within a group which
                    | is assigned the \"api\" middleware group. Enjoy building your API!
                    |
                    */
                    
                    Route::middleware('auth:api')->get('/user', function (Request $request) {
                        return $request->user();
                    });

            ");
            }
        }

        if(!$this->fs->isFile($routesPath)) {
            $routesPath = 'app/Http/routes.php';
        }

        $content = $this->fs->get($routesPath);

        $content .= PHP_EOL . $this->getTemplate($templateFile)
            ->with([
                'resource' => $resource,
                'controller' => $this->getController()
            ])
            ->get();

        $this->save($content, $routesPath, "{$resource} routes", true);
    }

    protected function getController()
    {
        $controller = $this->option('controller');
        if(! $controller){
            $controller = ucwords(str_plural(camel_case($this->argument('resource')))) . 'Controller';
        }
        return $controller;
    }

}
