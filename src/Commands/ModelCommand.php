<?php namespace Wn\Generators\Commands;


class ModelCommand extends BaseCommand {

	protected $signature = 'wn:model
        {name : Name of the model}
        {--fillable= : the fillable fields of the model}
        {--dates= : date fields of the model}
        {--path=app : where to store the model php file}';

	protected $description = 'Generates a model class for a RESTfull resource';

    protected $fields = [];

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->option('path');

        $content = $this->getTemplate('model')
            ->with([
                'name' => $name,
                'namespace' => $this->getNamespace(),
                'fillable' => $this->getAsArrayFields('fillable'),
                'dates' => $this->getAsArrayFields('dates')
            ])
            ->get();

        $this->save($content, "./{$path}/{$name}.php");

        $this->info("Model {$name} Generated !");
    }

    protected function getAsArrayFields($arg, $isOption = true)
    {
    	$arg = ($isOption) ? $this->option($arg) : $this->argument($arg);
        if(is_string($arg)){
        	$arg = explode(',', $arg);
        } else {
            $arg = [];
        }
        return implode(', ', array_map(function($item){
            return '"' . $item . '"';
        }, $arg));
    }

    protected function getNamespace()
    {
    	return str_replace(' ', '\\', ucwords(str_replace('/', ' ', $this->option('path'))));
    }
	
}