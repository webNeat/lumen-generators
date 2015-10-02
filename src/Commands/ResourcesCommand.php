<?php namespace Wn\Generators\Commands;

use Symfony\Component\Yaml\Yaml;


class ResourcesCommand extends BaseCommand {

    protected $signature = 'wn:resources
        {file : Path to the file containing resources declarations}';

    protected $description = 'Generates multiple resources from a file';

    public function handle()
    {
        $content = $this->fs->get($this->argument('file'));
        $content = Yaml::parse($content);

        foreach ($content as $model => $i){
            $i = $this->getResourceParams($model, $i);
            
            $this->call('wn:resource', [
                'name' => $i['name'],
                'fields' => $i['fields'],
                '--has-many' => $i['hasMany'],
                '--has-one' => $i['hasOne'],
                '--belongs-to' => $i['belongsTo']
            ]);
        }
    }

    protected function getResourceParams($modelName, $i)
    {
        $i['name'] = snake_case($modelName);

        foreach(['hasMany', 'hasOne', 'belongsTo'] as $relation){
            if(isset($i[$relation])){
                $i[$relation] = $this->convertArray($i[$relation], ' ', ',');
            } else {
                $i[$relation] = false;
            }
        }

        if($i['belongsTo']){
            $relations = $this->getArgumentParser('relations')->parse($i['belongsTo']);
            foreach ($relations as $relation){
                $foreignName = '';
                
                if(! $relation['model']){
                    $foreignName = snake_case($relation['name']) . '_id';
                } else {
                    $names = array_reverse(explode("\\", $relation['model']));
                    $foreignName = snake_case($names[0]) . '_id'; 
                }

                $i['fields'][$foreignName] = [
                    'schema' => 'integer',
                    'tags' => 'key'
                ];
            }
        }

        $fields = [];
        foreach($i['fields'] as $name => $value) {
            $value['name'] = $name;
            $fields[] = $this->serializeField($value);
        }
        $i['fields'] = implode(' ', $fields);

        return $i;
    }

    protected function serializeField($field)
    {
        $name = $field['name'];
        $schema = $this->convertArray(str_replace(':', '.', $field['schema']), ' ', ':');
        $rules = (isset($field['rules'])) ? trim($field['rules']) : '';
        $tags = $this->convertArray($field['tags'], ' ', ',');

        return "{$name};{$schema};{$rules};{$tags}";
    }

    protected function convertArray($list, $old, $new)
    {
        return implode($new, array_filter(explode($old, $list), function($item){
            return !empty($item);
        }));
    }

}
