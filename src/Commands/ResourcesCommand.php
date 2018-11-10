<?php namespace Wn\Generators\Commands;

use InvalidArgumentException;
use Symfony\Component\Yaml\Yaml;


class ResourcesCommand extends BaseCommand {

    protected $signature = 'wn:resources
        {files* : Paths to the files containing resources declarations}
        {--path= : where to store the model files.}
        {--routes= : where to store the routes.}
        {--no-routes : do not add routes.}
        {--controllers= : where to store the controllers.}
        {--no-controllers : do not generate controllers.}
        {--no-migration : do not migrate.}
        {--check-only : only check supplied files for valide relationships.}
        {--skip-check : skip validity check before processing.}
        {--force= : override the existing files}
        {--force-redefine : Force model redefinition.}
        {--laravel= : Use Laravel style route definitions}
    ';

    protected $description = 'Generates multiple resources from a couple of files';

    protected $pivotTables = [];
    protected $morphTables = [];

    private $checkedErrors = 0;
    private $checkErrors = [];
    private $checkInfo = [];

    public function handle()
    {
        $files = $this->argument('files');
        $nodes = [];

        if (empty($files)) {
            $this->error("No resource file(s) supplied!");
            return;
        }
        if (is_string($files)) {
            $files = [ $files ];
        }

        foreach ($files as $file) {
            $nodes = $this->mergeNodes($nodes, $this->readFile($file), $this->option('force-redefine'));
        }

        $this->line('');
        $this->info('Bringing models to order...');

        $nodes = $this->sortDependencies($nodes);
        $pivotTables = $this->uniqueArray($this->getTables($nodes, 'pivotTables'));
        $morphTables = $this->uniqueArray($this->getTables($nodes, 'morphTables'));

        if (! $this->option('skip-check')) {
        	$this->info('Checking Relationships...');
        	$keys = array_keys($nodes);
        	foreach ($nodes as $model => $i) {
        		$this->checkRelations($i['belongsTo'], 'belongsTo', $i['filename'], $i['uniquename'], $keys);
        		// $this->checkRelations($i['hasManyThrough'], 'hasManyThrough', $file, $model);
        	}
        	$this->checkPivotRelations($nodes, $pivotTables, 'pivot');
        	$this->checkPivotRelations($nodes, $morphTables, 'morph');
        }

        if ($this->checkedErrors > 0) {
        	$this->line('');
        	if ($this->option('check-only')) {
        		$this->info('Checking only, we have found ' . $this->checkedErrors . ' errors.');
        	}
        	$this->printErrors();
        }

        $proceed = (! $this->option('check-only') && $this->checkedErrors == 0) || $this->option('skip-check');
        if (! $this->option('check-only') && $this->checkedErrors > 0) {
        	$this->line('');
        	$proceed = $this->confirm("We have found " . $this->checkedErrors . " errors. Are you sure you want to continue?");
        }
        if ($proceed) {
        	$this->buildResources($nodes);

        	// if (!$this->option('no-migration')) {
        	// 	$this->call('migrate'); // actually needed for pivot seeders !
        	// }

        	$this->line('');
            $this->buildTables('Pivot-Table', 'wn:pivot-table', 'model1', 'model2', $pivotTables);

        	$this->line('');
            $this->buildTables('Morph-Table', 'wn:morph-table', 'model', 'morphable', $morphTables);

        	if (!$this->option('no-migration')) {
        		$this->call('migrate');
        	}
        }
    }

    protected function uniqueArray($array)
    {
        return array_map(
            'unserialize',
            array_unique(array_map('serialize', $array))
        );
    }

    protected function readFile($file)
    {
        $this->info("Reading file ".$file);

        $content = $this->fs->get($file);
        $content = Yaml::parse($content);

        $nodes = [];

        foreach ($content as $model => $i){
            /*
                $i['modelname'] = as originally in YAML defined
                $i['name']      = as originally defined in snake_case
                $i['uniquename']= for key in singular studly_case
            */
            $i['filename'] = $file;
            $i['modelname'] = $model;
            $model = studly_case(str_singular($model));
            $i['uniquename'] = $model;

            $nodes[] = $this->getResourceParams($i);
        }

        return $nodes;
    }

    protected function mergeNodes($nodes, $toMerge, $forceRedefinition = false) {
        foreach($toMerge as $node) {
            $nodes = $this->mergeNode($nodes, $node, $forceRedefinition);
        }

        return $nodes;
    }

    protected function mergeNode($nodes, $toMerge, $forceRedefinition = false) {
        if (empty($nodes[$toMerge['uniquename']]) || $forceRedefinition) {
            if (!empty($nodes[$toMerge['uniquename']])) {
                $this->checkError($toMerge['uniquename'] . ": forced to redefine (in file " . $nodes[$toMerge['uniquename']]['filename'] . ", redefined from file ".$toMerge['filename'].")");
            }
            $nodes[$toMerge['uniquename']] = $toMerge;
        } else {
            $this->checkError($toMerge['uniquename'] . ": already defined (in file " . $nodes[$toMerge['uniquename']]['filename'] . ", trying to redefine from file ".$toMerge['filename']."; Use --force-redefine to force redefinition)");
        }

        return $nodes;
    }

    protected function getTables($nodes, $key) {
        $tables = [];
        foreach($nodes as $node) {
            if (!empty($node[$key])) {
                $tables = array_merge($tables, $node[$key]);
            }
        }

        return $tables;
    }

    protected function buildResources($nodes)
    {
        $modelIndex = 0;
        $migrationIdLength = strlen((string)count($nodes));
        foreach ($nodes as $i) {
            $migrationName = 'Create' .  ucwords(str_plural($i['name']));
            $migrationFile = date('Y_m_d_His') . '-' . str_pad($modelIndex , $migrationIdLength, 0, STR_PAD_LEFT) . '_' . snake_case($migrationName) . '_table';

            $this->line('');
            $this->info('Building Model ' . $i['uniquename']);

            $options = [
                'name' => $i['name'],
                'fields' => $i['fields'],
                '--add' => $i['add'],
                '--has-many' => $i['hasMany'],
                '--has-one' => $i['hasOne'],
                '--belongs-to' => $i['belongsTo'],
                '--belongs-to-many' => $i['belongsToMany'],
                '--has-many-through' => $i['hasManyThrough'],
                '--morph-to' => $i['morphTo'],
                '--morph-many' => $i['morphMany'],
                '--morph-to-many' => $i['morphToMany'],
                '--morphed-by-many' => $i['morphedByMany'],
                '--no-routes' => $this->option('no-routes'),
                '--no-controller' => $this->option('no-controllers'),
                '--force' => $this->option('force'),
                '--migration-file' => $migrationFile,
            ];
            if ($this->option('laravel')) {
                $options['--laravel'] = true;
            }
            if ($this->option('routes')) {
                $options['--routes'] = $this->option('routes');
            }
            if ($this->option('controllers')) {
                $options['--controller'] = $this->option('controllers');
            }
            if ($this->option('path')) {
                $options['--path'] = $this->option('path');
            }

            $this->call('wn:resource', $options);
            $modelIndex++;
        }
    }

    protected function buildTables($type, $command, $model1, $model2, $tableAssignment)
    {
        foreach ($tableAssignment as $tables) {
            $this->info('Building '.$type.' ' . $tables[0] . ' - ' . $tables[1]);
            $this->call($command, [
                $model1 => $tables[0],
                $model2 => $tables[1],
                '--force' => $this->option('force')
            ]);

            // $this->call('wn:pivot-seeder', [
            //     'model1' => $tables[0],
            //     'model2' => $tables[1],
            //     '--force' => $this->option('force')
            // ]);
        }
    }

    protected function getResourceParams($i)
    {
        $modelName = $i['modelname'];

        $i['filename'] = $i['filename'];
        $i['name'] = snake_case($modelName);
        $i['modelname'] = $i['modelname'];
        $i['uniquename'] = $i['uniquename'];

        foreach(['hasMany', 'hasOne', 'add', 'belongsTo', 'belongsToMany', 'hasManyThrough', 'morphTo', 'morphMany', 'morphToMany', 'morphedByMany'] as $relation){
            if(isset($i[$relation])){
                $i[$relation] = $this->convertArray($i[$relation], ' ', ',');
            } else {
                $i[$relation] = false;
            }
        }

        if($i['belongsToMany']){
            $i['pivotTables'] = $this->belongsTo($i['name'], $modelName, $i['belongsToMany']);
        }

        if($i['morphToMany']){
            $i['morphTables'] = $this->morphToMany($modelName, $i['morphToMany']);
        }

        if($i['morphedByMany']){
            $i['morphTables'] = array_merge($i['morphTables'], $this->morphedByMany($i['name'], $modelName, $i['morphedByMany']));
        }

        $fields = [];
        foreach($i['fields'] as $name => $value) {
            $value['name'] = $name;
            $fields[] = $this->serializeField($value);
        }
        $i['fields'] = implode(' ', $fields);

        return $i;
    }

    protected function parseRelations($parser, $relations, $callback)
    {
        $parsedRelations = [];
        $relations = $this->getArgumentParser($parser)->parse($relations);
        foreach ($relations as $relation){
            $parsedRelations[] = $callback($relation);
        }

        return $parsedRelations;
    }

    protected function getConditionalTableName($condition, $then, $else)
    {
        if($condition){
            return snake_case($this->extractClassName($then));
        } else {
            return snake_case($this->extractClassName($else));
        }
    }

    protected function belongsTo($name, $modelName, $belongsTo)
    {
        return $this->parseRelations('relations', $belongsTo, function($relation) use ($name, $modelName) {
            $table = $this->getConditionalTableName(! $relation['model'], $relation['name'], $relation['model']);
            $tables = [ str_singular($table), $name ];
            sort($tables);
            $tables[] = $modelName;
            return $tables;
        });
    }

    protected function morphToMany($modelName, $morphToMany)
    {
        return $this->parseRelations('relations-morphMany', $morphToMany, function($relation) use ($modelName) {
            $name = $this->getConditionalTableName(! $relation['through'], $relation['name'], $relation['model']);
            return $this->getMorphableRelation($relation, $name, $modelName);
        });
    }

    protected function morphedByMany($name, $modelName, $morphedByMany)
    {
        return $this->parseRelations('relations-morphMany', $morphedByMany, function($relation) use ($name, $modelName) {
            return $this->getMorphableRelation($relation, $name, $modelName);
        });
    }

    protected function getMorphableRelation($relation, $relationName, $modelName) {
        $morphable = $this->getConditionalTableName(! $relation['through'], $relation['model'], $relation['through']);
        return [ str_singular($relationName), str_singular($morphable), $modelName ];
    }

    protected function serializeField($field)
    {
        $name = $field['name'];
        $schema = $this->convertArray(str_replace(':', '.', $field['schema']), ' ', ':');
        $rules = (isset($field['rules'])) ? $this->convertArray(trim($field['rules']), ' ', '|') : '';
        $tags = !empty($field['tags']) ? $this->convertArray($field['tags'], ' ', ',') : '';

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

    private function sortDependencies($nodes) {
        $load_order = array();
        $seen       = array();

        foreach($nodes as $key => $item) {
            $tmp = $this->getDependencies($nodes, $key, $seen);

            // if($tmp[2] === false) {
            $load_order = array_merge($load_order, $tmp[0]);
            $seen       = $tmp[1];
            // }
        }

        return $load_order;
    }

    private function getDependencies($nodes, $key, $seen = array()) {
        if(array_key_exists($key, $seen) === true) {
            return array(array(), $seen);
        }


        if(!empty($nodes[$key])) {
            $order = array();
            // $failed         = array();

            if($nodes[$key]['belongsTo']) {
                $deps = $this->getArgumentParser('relations')->parse($nodes[$key]['belongsTo']);
                foreach($deps as $dependency) {
                    if(! $dependency['model']){
	                    $dependency['model'] = $dependency['name'];
	                } else if(strpos($dependency['model'], '\\') !== false ){
	                    $dependency['model'] = substr($dependency['model'], strpos($dependency['model'], '\\')+1); // Cut offs first namespace part
	                }
                    $dependency['model'] = studly_case(str_singular($dependency['model']));
                    if ($dependency['model'] != $key) {
                        $tmp = $this->getDependencies($nodes, $dependency['model'], $seen);

                        $order  = array_merge($order, $tmp[0]);
                        $seen   = $tmp[1];
                    }

                    // if($tmp[2] !== false) {
                    //     $failed = array_merge($tmp[2], $failed);
                    // }
                }
            }
            $seen[$key]  = true;
            $order[$key] = $nodes[$key];
            // $failed     = (count($failed) > 0) ? $failed : false;

            return array($order, $seen);//, $failed
        }

        return array(array(), $seen);//, array($item)
    }

    protected function checkError($message, $model = "", $file = "") {
        $this->checkErrors[] = array("message" => $message, "model" => $model, "file" => $file);
        $this->checkedErrors++;
    }

    protected function checkInfo($message, $model = "", $file = "") {
        $this->checkInfo[] = array("message" => $message, "model" => $model, "file" => $file);
    }

    protected function printErrors() {
        foreach ($this->checkErrors as $error) {
            $this->error($error['message']);
        }
        foreach ($this->checkInfo as $info) {
            $this->info($info['message']);
        }
    }

    protected function checkRelations($relations, $type, $file, $model, $keys) {
        if ($relations) {
            $position = array_search($model, $keys);
            $relations = $this->getArgumentParser('relations')->parse($relations);
            foreach($relations as $relation) {
                $rModel = $relation['model'] ? $relation['model'] : $relation['name'];
                $search = array_search(studly_case(str_singular($rModel)), $keys);
                if (($search === false || $search > $position) && !class_exists($this->prependNamespace($rModel)) && !class_exists($this->prependNamespace($rModel, 'App'))) {
                    $this->checkError(studly_case(str_singular($rModel)) . ": undefined (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                } else if (class_exists($this->prependNamespace($rModel))) {
                    $this->checkInfo(studly_case(str_singular($rModel)) . ": already defined in Namespace " . $this->getNamespace() . " (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                } else if (class_exists($this->prependNamespace($rModel, 'App'))) {
                    $this->checkInfo(studly_case(str_singular($rModel)) . ": already defined in Namespace App\\ (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                }
            }
        }
    }

    protected function checkPivotRelations($nodes, $relations, $relationType) {
        if ($relations) {
            foreach($relations as $relation) {
                $relation['0'] = studly_case(str_singular($relation['0']));
                $relation['1'] = studly_case(str_singular($relation['1']));
                $relation['2'] = studly_case(str_singular($relation['2']));

                $this->checkRelation($nodes, $relationType, $relation['0'], $relation['2']);
                if ($relationType == "pivot") {
                    $this->checkRelation($nodes, $relationType, $relation['1'], $relation['2']);
                }
            }
        }
    }

    protected function checkRelation($nodes, $relationType, $relation, $model) {
        if (empty($nodes[$relation]) && !class_exists($this->getNamespace() . '\\' . $relation) && !class_exists('App\\' . $relation)) {
            $this->checkError($relation . ": undefined (used in " . $relationType . "-based relationship of model " . $model . " in file " . $nodes[$model]['filename'] . ")");
        } else if (class_exists($this->getNamespace() . '\\' . ucwords(camel_case($relation)))) {
            $this->checkInfo(studly_case(str_singular($relation)) . ": already defined in Namespace " . $this->getNamespace() . " (used in " . $relationType . "-based relationship of model " . $model . " in file " . $nodes[$model]['filename'] . ")");
        } else if (class_exists('App\\' . ucwords(camel_case($relation)))) {
            $this->checkInfo(studly_case(str_singular($relation)) . ": already defined in Namespace App\\ (used in " . $relationType . "-based relationship of model " . $model . " in file " . $nodes[$model]['filename'] . ")");
        }
    }

}
