<?php namespace Wn\Generators\Commands;

class ControllerCommand extends BaseCommand {

    const DEFAULT_PATH = "app/Http/Controllers";

    protected $signature = 'wn:controller
        {model : Name of the model (with namespace if not App)}
        {--path='.ControllerCommand::DEFAULT_PATH.' : where to store the controllers file.}
        {--no-routes= : without routes}
        {--routes= : where to store the routes.}
        {--force= : override the existing files}
        {--laravel : Use Laravel style route definitions}
    ';

    protected $description = 'Generates RESTful controller using the RESTActions trait';

    public function handle()
    {
        $model = $this->argument('model');
        $name = '';
        if(strrpos($model, "\\") === false){
            $name = $model;
            $model = "App\\" . $model;
        } else {
            $name = explode("\\", $model);
            $name = $name[count($name) - 1];
        }
        $controller = ucwords(str_plural($name)) . 'Controller';
        $content = $this->getTemplate('controller')
            ->with([
                'name' => $controller,
                'model' => $model,
                'namespace' => $this->getNamespace(),
                'use' => ($this->getNamespace() != $this->getDefaultNamespace()?'use '.$this->getDefaultNamespace().'\Controller;'.PHP_EOL.'use '.$this->getDefaultNamespace().'\RESTActions;'.PHP_EOL:'')
            ])
            ->get();

        $this->save($content, "./{$this->option('path')}/{$controller}.php", "{$controller}");

        if (! $this->option('no-routes')) {
            $options = [
                'resource' => snake_case($name, '-'),
                '--controller' => $controller,
            ];

            if ($this->option('laravel')) {
                $options['--laravel'] = true;
            }
            if ($this->option('routes')) {
                $options['--path'] = $this->option('routes');
            }
            if ($this->getNamespace() != $this->getDefaultNamespace()) {
                $options['--controller-namespace'] = $this->getNamespace();
            }

            $this->call('wn:route', $options);
        }
    }

    protected function getDefaultNamespace() {
        return $this->getNamespace(ControllerCommand::DEFAULT_PATH);
    }

}
