<?php namespace Wn\Generators\Commands;

use Symfony\Component\Yaml\Yaml;


class ResourcesCommand extends BaseCommand {

    protected $signature = 'wn:resources
        {file : Path to the file containing resources declarations}
        {--path=app : where to store the model files.}
        {--force= : override the existing files}
    ';

    protected $description = 'Generates multiple resources from a file';

    protected $pivotTables = [];

    public function handle()
    {
        $content = $this->fs->get($this->argument('file'));
        $content = Yaml::parse($content);

        $modelIndex = 0;
        foreach ($content as $model => $i){
            $i = $this->getResourceParams($model, $i);
            $migrationName = 'Create' .  ucwords(str_plural($i['name']));
            $migrationFile = date('Y_m_d_His') . '-' . str_pad($modelIndex , 3, 0, STR_PAD_LEFT) . '_' . snake_case($migrationName) . '_table';


            $this->call('wn:resource', [
                'name' => $i['name'],
                'fields' => $i['fields'],
                '--add' => $i['add'],
                '--has-many' => $i['hasMany'],
                '--has-one' => $i['hasOne'],
                '--belongs-to' => $i['belongsTo'],
                '--belongs-to-many' => $i['belongsToMany'],
                '--path' => $this->option('path'),
                '--force' => $this->option('force'),
                '--migration-file' => $migrationFile
            ]);
            $modelIndex++;
        }

        // $this->call('migrate'); // actually needed for pivot seeders !

        $this->pivotTables = array_map(
            'unserialize',
            array_unique(array_map('serialize', $this->pivotTables))
        );

        foreach ($this->pivotTables as $tables) {
            $this->call('wn:pivot-table', [
                'model1' => $tables[0],
                'model2' => $tables[1],
                '--force' => $this->option('force')
            ]);

            // $this->call('wn:pivot-seeder', [
            //     'model1' => $tables[0],
            //     'model2' => $tables[1],
            //     '--force' => $this->option('force')
            // ]);
        }

        $this->call('migrate');
    }

    protected function getResourceParams($modelName, $i)
    {
        $i['name'] = snake_case($modelName);

        foreach(['hasMany', 'hasOne', 'add', 'belongsTo', 'belongsToMany'] as $relation){
            if(isset($i[$relation])){
                $i[$relation] = $this->convertArray($i[$relation], ' ', ',');
            } else {
                $i[$relation] = false;
            }
        }

        if($i['belongsToMany']){
            $relations = $this->getArgumentParser('relations')->parse($i['belongsToMany']);
            foreach ($relations as $relation){
                $table = '';

                if(! $relation['model']){
                    $table = snake_case($relation['name']);
                } else {
                    $names = array_reverse(explode("\\", $relation['model']));
                    $table = snake_case($names[0]);
                }

                $tables = [ str_singular($table), $i['name'] ];
                sort($tables);
                $this->pivotTables[] = $tables;
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
        // Replace space by comma
        $rules = str_replace(' ', ',', $rules);

        $tags = $this->convertArray($field['tags'], ' ', ',');

        $string = "{$name};{$schema};{$rules};{$tags}";

        if(isset($field['factory']) && !empty($field['factory'])){
            $string .= ';' . $field['factory'];
        }

        return $string;
    }

    protected function convertArray($list, $old, $new)
    {
        return implode($new, array_filter(explode($old, $list), function($item){
            return !empty($item);
        }));
    }

}
