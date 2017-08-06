<?php namespace Wn\Generators\Commands;


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
        $laravelRoutes = $this->option('laravel');
        $templateFile = 'routes';

        $routesPath = './routes/web.php';
        if ($laravelRoutes) {
            $routesPath = './routes/api.php';
            $templateFile = 'routes-laravel';
        }
        if (! $this->fs->exists($routesPath))
            $routesPath = './app/Http/routes.php';

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
