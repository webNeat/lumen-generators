<?php namespace Wn\Generators\Commands;


class RouteCommand extends BaseCommand {

	protected $signature = 'wn:route
		{resource : Name of the resource.}
        {--controller= : Name of the RESTful controller.}';

	protected $description = 'Generates RESTful routes.';

    public function handle()
    {
        $resource = $this->argument('resource');

        $content = $this->fs->get('./app/Http/routes.php');

        $content .= PHP_EOL . $this->getTemplate('routes')
            ->with([
                'resource' => $resource,
                'controller' => $this->getController()
            ])
            ->get();

        $this->save($content, './app/Http/routes.php', "{$resource} routes", true);
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