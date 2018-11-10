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

    private $nodes = [];
    private $checkedErrors = 0;
    private $checkErrors = [];
    private $checkInfo = [];

    public function handle()
    {
        $files = $this->argument('files');
        $this->nodes = [];
        foreach ($files as $file) {
        	$this->info("Reading file ".$file);

        	$content = $this->fs->get($file);
        	$content = Yaml::parse($content);

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

        		if (empty($this->nodes[$model]) || $this->option('force-redefine')) {
        			if (!empty($this->nodes[$model])) {
        				$this->checkError($model . ": forced to redefine (in file " . $this->nodes[$model]['filename'] . ", redefined from file ".$file.")");
        			}
        			$i = $this->getResourceParams($i);
        			$this->nodes[$model] = $i;
        		} else {
        			$this->checkError($model . ": already defined (in file " . $this->nodes[$model]['filename'] . ", trying to redefine from file ".$file."; Use --force-redefine to force redefinition)");
        		}
        	}
        }

        $this->line('');
        $this->info('Bringing models to order...');

        $this->nodes = $this->sortDependencies();

        if (! $this->option('skip-check')) {
        	$this->info('Checking Relationships...');
        	$keys = array_keys($this->nodes);
        	foreach ($this->nodes as $model => $i) {
        		$this->checkRelations($i['belongsTo'], 'belongsTo', $i['filename'], $i['uniquename'], $keys);
        		// $this->checkRelations($i['hasManyThrough'], 'hasManyThrough', $file, $model);
        	}
        	$this->checkPivotRelations($this->pivotTables, 'pivot');
        	$this->checkPivotRelations($this->morphTables, 'morph');
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
        	$modelIndex = 0;
            $migrationIdLength = strlen((string)count($this->nodes));
        	foreach ($this->nodes as $i) {
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

        	// if (!$this->option('no-migration')) {
        	// 	$this->call('migrate'); // actually needed for pivot seeders !
        	// }

        	$this->pivotTables = array_map(
        		'unserialize',
        		array_unique(array_map('serialize', $this->pivotTables))
        	);

        	$this->line('');
        	foreach ($this->pivotTables as $tables) {
        		$this->info('Building Pivot-Table ' . $tables[0] . ' - ' . $tables[1]);
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

        	$this->morphTables = array_map(
        		'unserialize',
        		array_unique(array_map('serialize', $this->morphTables))
        	);

        	$this->line('');
        	foreach ($this->morphTables as $tables) {
        		$this->info('Building Morph-Table ' . $tables[0] . ' - ' . $tables[1]);
        		$this->call('wn:morph-table', [
        			'model' => $tables[0],
        			'morphable' => $tables[1],
        			'--force' => $this->option('force')
        		]);

        		// $this->call('wn:pivot-seeder', [
        		//     'model1' => $tables[0],
        		//     'model2' => $tables[1],
        		//     '--force' => $this->option('force')
        		// ]);
        	}

        	if (!$this->option('no-migration')) {
        		$this->call('migrate');
        	}
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
                $tables[] = $modelName;
                $this->pivotTables[] = $tables;
            }
        }

        if($i['morphToMany']){
            $relations = $this->getArgumentParser('relations-morphMany')->parse($i['morphToMany']);
            foreach ($relations as $relation){
                $table = '';

                if(! $relation['through']){
                    $names = array_reverse(explode("\\", $relation['model']));
                    $morphable = snake_case($names[0]);
                    $model = snake_case($relation['name']);
                } else {
                    $names = array_reverse(explode("\\", $relation['through']));
                    $morphable = snake_case($names[0]);
                    $names = array_reverse(explode("\\", $relation['model']));
                    $model = snake_case($names[0]);
                }

                $tables = [ str_singular($model), str_singular($morphable), $modelName ];
                $this->morphTables[] = $tables;
            }
        }

        if($i['morphedByMany']){
            $relations = $this->getArgumentParser('relations-morphMany')->parse($i['morphedByMany']);
            foreach ($relations as $relation){
                $table = '';

                if(! $relation['through']){
                    $names = array_reverse(explode("\\", $relation['model']));
                    $morphable = snake_case($names[0]);
                } else {
                    $names = array_reverse(explode("\\", $relation['through']));
                    $morphable = snake_case($names[0]);
                }

                $tables = [ str_singular($i['name']), str_singular($morphable), $modelName ];
                $this->morphTables[] = $tables;
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

    private function sortDependencies() {
        $load_order = array();
        $seen       = array();

        foreach($this->nodes as $key => $item) {
            $tmp = $this->getDependencies($key, $seen);

            // if($tmp[2] === false) {
            $load_order = array_merge($load_order, $tmp[0]);
            $seen       = $tmp[1];
            // }
        }

        return $load_order;
    }

    private function getDependencies($key, $seen = array()) {
        if(array_key_exists($key, $seen) == true) {
            return array(array(), $seen);
        }


        if(!empty($this->nodes[$key])) {
            $order = array();
            // $failed         = array();

            if($this->nodes[$key]['belongsTo']) {
                $deps = $this->getArgumentParser('relations')->parse($this->nodes[$key]['belongsTo']);
                foreach($deps as $dependency) {
                    if(! $dependency['model']){
	                    $dependency['model'] = $dependency['name'];
	                } else if(strpos($dependency['model'], '\\') !== false ){
	                    $dependency['model'] = substr($dependency['model'], strpos($dependency['model'], '\\')+1);
	                }
                    $dependency['model'] = studly_case(str_singular($dependency['model']));
                    if ($dependency['model'] != $key) {
                        $tmp = $this->getDependencies($dependency['model'], $seen);

                        $order  = array_merge($order, $tmp[0]);
                        $seen   = $tmp[1];
                    }

                    // if($tmp[2] !== false) {
                    //     $failed = array_merge($tmp[2], $failed);
                    // }
                }
            }
            $seen[$key]  = true;
            $order[$key] = $this->nodes[$key];
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
                switch($type) {
                    case "belongsTo":
                        $rModel = $relation['model']?$relation['model']:$relation['name']; break;
                }

                $search = array_search(studly_case(str_singular($rModel)), $keys);
                if (($search === false || $search > $position) && !class_exists($this->getNamespace() . '\\' . ucwords(camel_case($rModel))) && !class_exists('App\\' . ucwords(camel_case($rModel)))) {
                    $this->checkError(studly_case(str_singular($rModel)) . ": undefined (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                } else if (class_exists($this->getNamespace() . '\\' . ucwords(camel_case($rModel)))) {
                    $this->checkInfo(studly_case(str_singular($rModel)) . ": already defined in Namespace " . $this->getNamespace() . " (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                } else if (class_exists('App\\' . ucwords(camel_case($rModel)))) {
                    $this->checkInfo(studly_case(str_singular($rModel)) . ": already defined in Namespace App\\ (used in " . $type . "-relationship of model " . $model . " in file " . $file . ")");
                }
            }
        }
    }

    protected function checkPivotRelations($relations, $rType) {
        if ($relations) {
            foreach($relations as $relation) {
                $relation['0'] = studly_case(str_singular($relation['0']));
                $relation['1'] = studly_case(str_singular($relation['1']));
                $relation['2'] = studly_case(str_singular($relation['2']));

                if (empty($this->nodes[$relation['0']]) && !class_exists($this->getNamespace() . '\\' . ucwords(camel_case($relation['0']))) && !class_exists('App\\' . ucwords(camel_case($relation['0'])))) {
                    $this->checkError($relation['0'] . ": undefined (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                } else if (class_exists($this->getNamespace() . '\\' . ucwords(camel_case($relation['0'])))) {
                    $this->checkInfo(studly_case(str_singular($relation['0'])) . ": already defined in Namespace " . $this->getNamespace() . " (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                } else if (class_exists('App\\' . ucwords(camel_case($relation['0'])))) {
                    $this->checkInfo(studly_case(str_singular($relation['0'])) . ": already defined in Namespace App\\ (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                }

                if ($rType == "pivot" && empty($this->nodes[$relation['1']]) && !class_exists($this->getNamespace() . '\\' . ucwords(camel_case($relation['1']))) && !class_exists('App\\' . ucwords(camel_case($relation['1'])))) {
                    $this->checkError($relation['1'] . ": undefined (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                } else if ($rType == "pivot" && class_exists($this->getNamespace() . '\\' . ucwords(camel_case($relation['1'])))) {
                    $this->checkInfo(studly_case(str_singular($relation['1'])) . ": already defined in Namespace " . $this->getNamespace() . " (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                } else if ($rType == "pivot" && class_exists('App\\' . ucwords(camel_case($relation['1'])))) {
                    $this->checkInfo(studly_case(str_singular($relation['1'])) . ": already defined in Namespace App\\ (used in " . $rType . "-based relationship of model " . $relation['2'] . " in file " . $this->nodes[$relation['2']]['filename'] . ")");
                }
            }
        }
    }

}
