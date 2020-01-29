<?php namespace Wn\Generators\Commands;


use InvalidArgumentException;

class ControllerCommand extends BaseCommand {

	protected $signature = 'wn:controller
        {model : Name of the model (with namespace if not App)}
		{--no-routes= : without routes}
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
        $controller = ucwords(\Illuminate\Support\Str::plural($name)) . 'Controller';
        $content = $this->getTemplate('controller')
        	->with([
        		'name' => $controller,
        		'model' => $model
        	])
        	->get();

        $this->save($content, "./app/Http/Controllers/{$controller}.php", "{$controller}");
        if(! $this->option('no-routes')){
            $options = [
                'resource' => \Illuminate\Support\Str::snake($name, '-'),
                '--controller' => $controller,
            ];

            if ($this->option('laravel')) {
                $options['--laravel'] = true;
            }

            $this->call('wn:route', $options);
        }
    }

}
