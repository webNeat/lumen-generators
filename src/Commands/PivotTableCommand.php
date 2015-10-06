<?php namespace Wn\Generators\Commands;


class PivotTableCommand extends BaseCommand {

	protected $signature = 'wn:pivot-table
        {model1 : Name of the first model or table}
        {model2 : Name of the second model or table}
        {--file= : name of the migration file.}
        ';

	protected $description = 'Generates creation migration for a pivot table';

    protected $tables;

    public function handle()
    {
        $this->parseTables();

        $this->call('wn:migration', [
            'table' => implode('_', $this->tables),
            '--schema' => $this->schema(),
            '--keys' => $this->keys(),
            '--file' => $this->option('file'),
            '--parsed' => false
        ]);
    }

    protected function parseTables()
    {
        $this->tables = array_map(function($arg) {
            return snake_case(str_singular($this->argument($arg)));
        }, ['model1', 'model2']);
        
        sort($this->tables);
    }

    protected function schema()
    {
        return implode(' ', array_map(function($table){
            return $table . '_id:integer:unsigned:index';
        }, $this->tables));
    }

    protected function keys()
    {
        return implode(' ', array_map(function($table){
            return $table . '_id';
        }, $this->tables));
    }
    
}