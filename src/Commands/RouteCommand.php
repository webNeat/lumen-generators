<?php namespace Wn\Generators\Commands;


class RouteCommand extends BaseCommand {

	protected $signature = 'wn:route
		{resource : Name of the resource.}
        {--controller= : Name of the RESTful controller.}';

	protected $description = 'Generates RESTful routes.';

    public function handle()
    {
        $resource = $this->argument('resource');

        $routesPath = './routes/web.php';
        if (! $this->fs->exists($routesPath))
            $routesPath = './app/Http/routes.php';

        $content = $this->fs->get($routesPath);

        $content .= PHP_EOL . $this->getTemplate('routes')
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
