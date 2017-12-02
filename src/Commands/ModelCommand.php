<?php namespace Wn\Generators\Commands;


class ModelCommand extends BaseCommand {

	protected $signature = 'wn:model
        {name : Name of the model.}
        {--fillable= : the fillable fields.}
        {--dates= : date fields.}
        {--has-many= : hasMany relationships.}
        {--has-one= : hasOne relationships.}
        {--belongs-to= : belongsTo relationships.}
        {--belongs-to-many= : belongsToMany relationships.}
        {--rules= : fields validation rules.}
        {--timestamps=true : enables timestamps on the model.}
        {--path=app : where to store the model php file.}
        {--soft-deletes= : adds SoftDeletes trait to the model.}
        {--parsed : tells the command that arguments have been already parsed. To use when calling the command from an other command and passing the parsed arguments and options}
        {--force= : override the existing files}
    ';

	protected $description = 'Generates a model class for a RESTfull resource';

    public function handle()
    {
        $name = $this->argument('name');
        $path = $this->option('path');

        $content = $this->getTemplate('model')
            ->with([
                'name' => $name,
                'namespace' => $this->getNamespace(),
                'required' => $this->getFieldByRuleAsArrayFields('required'),
                'fillable' => $this->getAsArrayFields('fillable'),
                'dates' => $this->getAsArrayFields('dates'),
                'relations' => $this->getRelations(),
                'rules' => $this->getRules(),
                'additional' => $this->getAdditional(),
                'uses' => $this->getUses()
            ])
            ->get();

        $this->save($content, "./{$path}/{$name}.php", "{$name} model");
    }

    protected function getFieldByRuleAsArrayFields($rule, $allowPartialMatch = false)
    {
        $rules = $this->getRules(false);
        $fields = [];
                
        foreach($rules as $ruleId => $ruleData){
            $fieldRules = explode(',', $ruleData['rule']);
            
            foreach($fieldRules as $fieldRule){
                if($fieldRule === $rule || str_contains($fieldRule, $rule)){
                    $fields[] = $ruleData['name'];
                }
            }
        }
        
        return implode(', ', array_map(function($item){
            return '"' . $item . '"';
        }, $fields));
    }

    protected function getAsArrayFields($arg, $isOption = true)
    {
    	$arg = ($isOption) ? $this->option($arg) : $this->argument($arg);
        if(is_string($arg)){
        	$arg = explode(',', $arg);
        } else if(! is_array($arg)) {
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
            $this->getRelationsByType('belongsTo', 'belongs-to'),
            $this->getRelationsByType('belongsToMany', 'belongs-to-many', true)
        );

        if(empty($relations)){
            return "    // Relationships";
        }

        return implode(PHP_EOL, $relations);
    }

    protected function getRelationsByType($type, $option, $withTimestamps = false)
    {
        $relations = [];
        $option = $this->option($option);
        if($option){

            $items = $this->getArgumentParser('relations')->parse($option);

            $template = ($withTimestamps) ? 'model/relation-with-timestamps' : 'model/relation';
            $template = $this->getTemplate($template);
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

    protected function getRules($inTemplate = true)
    {
        $rules = $this->option('rules');
        if(! $rules){
            return "        // Validation rules";
        }
        $items = $rules;
        if(! $this->option('parsed')){
            $items = $this->getArgumentParser('rules')->parse($rules);
        }
        $model = $this->argument('name');
        $rules = [];
        $template = $this->getTemplate('model/rule');
        foreach ($items as $item) {
            $rules[] = $template->with(array_merge(
                $item,
                [ 'model' => $model ]
            ))->get();
        }

        return $inTemplate ? implode(PHP_EOL, $rules) : $items;
    }

    protected function getAdditional()
    {
        return $this->option('timestamps') == 'false'
            ? "    public \$timestamps = false;" . PHP_EOL . PHP_EOL
            : '';
    }

    protected function getUses()
    {
        return $this->option('soft-deletes') == 'true'
            ? '    use \Illuminate\Database\Eloquent\SoftDeletes;' . PHP_EOL . PHP_EOL
            : '';
    }

}
