<?php namespace Wn\Generators\Commands;


class ControllerCommand extends BaseCommand {

	protected $signature = 'wn:controller
		{model : Name of the model (with namespace if not App;}';

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
    	$name = ucwords(str_plural($name));
        $content = $this->getTemplate('controller')
        	->with([
        		'name' => $name,
        		'model' => $model
        	])
        	->get();

        $this->save($content, "./app/Http/Controllers/{$name}Controller.php");

        $this->info("{$name}Controller generated !");
    }
    
}