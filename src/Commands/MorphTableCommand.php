<?php namespace Wn\Generators\Commands;


class MorphTableCommand extends BaseCommand {

    protected $signature = 'wn:morph-table
        {model : Name of the persistant model or table}
        {morphable : Name of the morphable identifier}
        {--add= : specifies additional columns like timestamps, softDeletes, rememberToken and nullableTimestamps.}
        {--file= : name of the migration file (to use only for testing purpose).}
        {--force= : override the existing files}
    ';

    protected $description = 'Generates creation migration for a morphable pivot table';

    protected $fields;

    public function handle()
    {
        $this->parseFields();

        $this->call('wn:migration', [
            'table' => snake_case(str_plural($this->argument('morphable'))),
            '--schema' => $this->schema(),
            '--keys' => $this->keys(),
            '--file' => $this->option('file'),
            '--parsed' => false,
            '--force' => $this->option('force'),
            '--add' => $this->option('add')
        ]);
    }

    protected function parseFields()
    {
        $this->fields = array_map(function($arg, $app) {
            return snake_case(str_singular($this->argument($arg))) . "_" . $app;
        }, ['model', 'morphable', 'morphable'], ['id', 'id', 'type']);

    }

    protected function schema()
    {
        return implode(' ', array_map(function($field) {
            return $field . ':' . (substr($field, -3) == '_id' ? 'integer:unsigned' : 'string.50') . ':index';
        }, $this->fields));
    }

    protected function keys()
    {
        // return implode(' ', $this->fields);
        return snake_case(str_singular($this->argument('model'))) . "_id";
    }

}
