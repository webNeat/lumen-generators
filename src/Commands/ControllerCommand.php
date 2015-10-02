<?php namespace Wn\Generators\Commands;


class ControllerCommand extends BaseCommand {

	protected $signature = 'wn:controller
        {model : Name of the model (with namespace if not App)}
		{--no-routes= : without routes}';

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
        		'model' => $model
        	])
        	->get();

        $this->save($content, "./app/Http/Controllers/{$controller}.php");

        $this->info("{$controller} generated !");

        if(! $this->option('no-routes')){
            $this->call('wn:route', [
                'resource' => snake_case($name, '-'),
                '--controller' => $controller
            ]);
        }
    }
    
}