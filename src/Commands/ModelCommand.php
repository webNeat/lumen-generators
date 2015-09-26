<?php namespace Wn\Generators\Commands;


class ModelCommand extends BaseCommand {

	protected $signature = 'wn:model
        {name : Name of the model.}
        {--fillable= : the fillable fields.}
        {--dates= : date fields.}
        {--has-many= : hasMany relationships.}
        {--has-one= : hasOne relationships.}
        {--belongs-to= : belongsTo relationships.}
        {--rules= : fields validation rules.}
        {--path=app : where to store the model php file.}';

	protected $description = 'Generates a model class for a RESTfull resource';

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->option('path');

        $content = $this->getTemplate('model')
            ->with([
                'name' => $name,
                'namespace' => $this->getNamespace(),
                'fillable' => $this->getAsArrayFields('fillable'),
                'dates' => $this->getAsArrayFields('dates'),
                'relations' => $this->getRelations(),
                'rules' => $this->getRules()
            ])
            ->get();

        $this->save($content, "./{$path}/{$name}.php");

        $this->info("{$name} model generated !");
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
	
    protected function getRelations()
    {
        $relations = array_merge([], 
            $this->getRelationsByType('hasOne', 'has-one'),
            $this->getRelationsByType('hasMany', 'has-many'),
            $this->getRelationsByType('belongsTo', 'belongs-to')
        );

        if(empty($relations)){
            return "\t// Relationships";
        }

        return implode(PHP_EOL, $relations);
    }

    protected function getRelationsByType($type, $option)
    {
        $relations = [];
        $option = $this->option($option);
        if($option){
            $parser = $this->getArgumentParser('relations');
            $template = $this->getTemplate('model/relation');
            $items = $parser->parse($option);
            foreach ($items as $item) {
                $item['type'] = $type;
                if(! $item['model']){
                    $item['model'] = $this->getNamespace() . '\\' . ucwords(str_singular($item['name']));
                } else if(strpos($item['model'], '\\') === false ){
                    $item['model'] = $this->getNamespace() . '\\' . $item['model'];
                }
                $relations[] = $template->with($item)->get();
            }
        }
        return $relations;
    }

    protected function getRules()
    {
        $rules = $this->option('rules');
        if(! $rules){
            return "\t\t// Validation rules";
        }
        $parser = $this->getArgumentParser('rules');
        $template = $this->getTemplate('model/rule');
        $items = $parser->parse($rules);
        $rules = [];
        foreach ($items as $item) {
            $rules[] = $template->with($item)->get();
        }

        return implode(PHP_EOL, $rules);
    }
    
}