<?php namespace Wn\Generators\Commands;


use InvalidArgumentException;

class ResourceCommand extends BaseCommand {

    protected $signature = 'wn:resource
        {name : Name of the resource.}
        {fields : fields description.}
        {--has-many= : hasMany relationships.}
        {--has-one= : hasOne relationships.}
        {--belongs-to= : belongsTo relationships.}
        {--belongs-to-many= : belongsToMany relationships.}
        {--has-many-through= : hasManyThrough relationships.}
        {--morph-to= : morphTo relationships.}
        {--morph-many= : morphMany relationships.}
        {--morph-to-many= : morphToMany relationships.}
        {--morphed-by-many= : morphedByMany relationships.}
        {--migration-file= : the migration file name.}
        {--add= : specifies additional columns like timestamps, softDeletes, rememberToken and nullableTimestamps.}
        {--path=app : where to store the model file.}
        {--routes= : where to store the routes.}
        {--no-routes : do not add routes.}
        {--controller= : where to store the controllers file.}
        {--no-controller : do not generate controllers.}
        {--parsed : tells the command that arguments have been already parsed. To use when calling the command from an other command and passing the parsed arguments and options}
        {--force= : override the existing files}
        {--laravel= : Use Laravel style route definitions}
    ';

    protected $description = 'Generates a model, migration, controller and routes for RESTful resource';

    protected $fields;

    public function handle()
    {
        $this->parseFields();

        $resourceName = $this->argument('name');
        $modelName = ucwords(camel_case($resourceName));
        $tableName = snake_case(str_plural($resourceName));

        // generating the model
        $this->call('wn:model', [
            'name' => $modelName,
            '--fillable' => $this->fieldsHavingTag('fillable'),
            '--dates' => $this->fieldsHavingTag('date'),
            '--has-many' => $this->option('has-many'),
            '--has-one' => $this->option('has-one'),
            '--belongs-to' => $this->option('belongs-to'),
            '--belongs-to-many' => $this->option('belongs-to-many'),
            '--has-many-through' => $this->option('has-many-through'),
            '--morph-to' => $this->option('morph-to'),
            '--morph-many' => $this->option('morph-many'),
            '--morph-to-many' => $this->option('morph-to-many'),
            '--morphed-by-many' => $this->option('morphed-by-many'),
            '--rules' => $this->rules(),
            '--path' => $this->option('path'),
            '--force' => $this->option('force'),
            '--timestamps' => $this->hasTimestamps() ? 'true' : 'false',
            '--soft-deletes' => $this->hasSoftDeletes() ? 'true' : 'false',
            '--parsed' => true
        ]);

        // generating the migration
        $this->call('wn:migration', [
            'table' => $tableName,
            '--schema' => $this->schema(),
            '--keys' => $this->migrationKeys(),
            '--file' => $this->option('migration-file'),
            '--force' => $this->option('force'),
            '--add' => $this->option('add'),
            '--parsed' => true
        ]);

        if (! $this->option('no-controller')) {
            // generating REST actions trait if doesn't exist
            if(! $this->fs->exists('./app/Http/Controllers/RESTActions.php')){
                $this->call('wn:controller:rest-actions');
            }

            // generating the controller and routes
            $controllerOptions = [
                'model' => $modelName,
                '--force' => $this->option('force'),
                '--no-routes' => $this->option('no-routes'),
            ];
            if ($this->option('laravel')) {
                $controllerOptions['--laravel'] = true;
            }
            if ($this->option('routes')) {
                $controllerOptions['--routes'] = $this->option('routes');
            }
            if ($this->option('controller')) {
                $controllerOptions['--path'] = $this->option('controller');
            }
            $this->call('wn:controller', $controllerOptions);
        }

        // generating model factory
        $this->call('wn:factory', [
            'model' => $this->getNamespace().$modelName,
            '--file' => './database/factories/'.str_plural($modelName).'.php',
            '--fields' => $this->factoryFields(),
            '--force' => $this->option('force'),
            '--parsed' => true
        ]);

        // generating database seeder
        // $this->call('wn:seeder', [
        //     'model' => 'App\\' . $modelName
        // ]);

    }

    protected function getNamespace()
    {
    	return str_replace(' ', '\\', ucwords(trim(str_replace('/', ' ', $this->option('path'))))).'\\';
    }

    protected function parseFields()
    {
        $fields = $this->argument('fields');
        if($this->option('parsed')){
            $this->fields = $fields;
        } else {
            if(! $fields){
                $this->fields = [];
            } else {
                $this->fields = $this->getArgumentParser('fields')
                    ->parse($fields);
            }
            $this->fields = array_merge($this->fields, array_map(function($name) {
                $return = [
                    'name' => $name['name'],
                    'schema' => [],
                    'rules' => '',
                    'tags' => ['fillable', 'key'],
                    'factory' => ''
                ];

                if ($name['type'] == 'morphTo') {
                    if (substr($name['name'], -3) == "_id") {
                        $return['schema'] = [
                            ['name' => 'integer', 'args' => []],
                            ['name' => 'unsigned', 'args' => []]
                        ];
                        $return['rules'] = 'numeric';
                        $return['factory'] = 'key';
                    } else {
                        $return['schema'] = [
                            ['name' => 'string', 'args' => ['50']]
                        ];
                    }
                    if ($name['nullable']) {
                        $return['schema'][] = ['name' => 'nullable', 'args' => []];
                    } else {
                        $return['rules'] = 'required'.(!empty($return['rules'])?'|'.$return['rules']:'');
                    }
                } else {
                    $return['schema'] = [
                        ['name' => 'integer', 'args' => []],
                        ['name' => 'unsigned', 'args' => []]
                    ];
                    $return['rules'] = 'required|numeric';
                    $return['factory'] = 'key';
                }

                return $return;
            }, $this->foreignKeys()));
        }

    }

    protected function fieldsHavingTag($tag)
    {
        return array_map(function($field){
            return $field['name'];
        }, array_filter($this->fields, function($field) use($tag){
            return in_array($tag, $field['tags']);
        }));
    }

    protected function rules()
    {
        return array_map(function($field){
            return [
                'name' => $field['name'],
                'rule' => $field['rules']
            ];
        }, array_filter($this->fields, function($field){
            return !empty($field['rules']);
        }));
    }

    protected function schema()
    {
        return array_map(function($field){
            return array_merge([[
                'name' => $field['name'],
                'args' => []
            ]], $field['schema']);
        }, $this->fields);
    }

    protected function getBaseModel($path) {
        $index = strrpos($path, "\\");
        if($index) {
            return substr($path, $index + 1);
        }
        return $path;
    }

    protected function foreignKeys($withMorph = true)
    {
        $belongsTo = $this->option('belongs-to');
        $morphTo = $this->option('morph-to');

        if(! $belongsTo && (! $withMorph || ! $morphTo)) {
            return [];
        }

        $belongsTo = $belongsTo ? $this->getArgumentParser('relations')->parse($belongsTo) : [];

        $belongsTo = array_map(function($relation){
            return array("model" => camel_case(str_singular($this->getBaseModel($relation['model'] ? $relation['model'] : $relation['name']))), "name" => snake_case(str_singular($this->getBaseModel($relation['name']))) . '_id', "type" => "belongsTo");
        }, $belongsTo);

        if ($withMorph) {
            $morphTo = $morphTo ? $this->getArgumentParser('relations-morphTo')->parse($morphTo) : [];
            $morphTo = array_map(function($relation){
                $name = snake_case(str_singular($relation['name']));
                return array(array("name" => $name . '_id', "type" => "morphTo", "nullable" => $relation['nullable']), array("name" => $name . '_type', "type" => "morphTo", "nullable" => $relation['nullable']));
            }, $morphTo);

            // $morphed = [];
            // array_walk_recursive($morphTo, function($a) use (&$morphed) { $morphed[] = $a; });
            $morphed = !empty($morphTo) ? call_user_func_array('array_merge', $morphTo) : array();

            return array_merge($belongsTo, $morphed);
        }

        return $belongsTo;
    }

    protected function migrationKeys() {
        return array_map(function($name) {
            return [
                'name' => snake_case($name['name']),
                'column' => '',
                'table' => snake_case(str_plural($name['model'])),
                'on_delete' => '',
                'on_update' => ''
            ];
        }, $this->foreignKeys(false));
    }

    protected function factoryFields()
    {
        return array_map(function($field){
            return [
                'name' => $field['name'],
                'type' => $field['factory']
            ];
        }, array_filter($this->fields, function($field){
            return isset($field['factory']) && $field['factory'];
        }));
    }

    protected function hasTimestamps()
    {
        $additionals = explode(',', $this->option('add'));
        return in_array('nullableTimestamps', $additionals)
            || in_array('timestamps', $additionals)
            || in_array('timestampsTz', $additionals);
    }

    protected function hasSoftDeletes()
    {
        $additionals = explode(',', $this->option('add'));
        return in_array('softDeletes', $additionals);
    }

}
