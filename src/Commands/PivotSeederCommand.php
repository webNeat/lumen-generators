<?php namespace Wn\Generators\Commands;


class PivotSeederCommand extends BaseCommand {

	protected $signature = 'wn:pivot-seeder
        {model1 : Name of the first model or table}
        {model2 : Name of the second model or table}
        {--count=10 : number of elements to add in database.}
        {--force= : override the existing files}
    ';

	protected $description = 'Generates seeder for pivot table';

    public function handle()
    {
        $resources = $this->getResources();
        $name = $this->getSeederName($resources);
        $tables = $this->getTableNames($resources);
        $file = "./database/seeds/{$name}.php";

        $content = $this->getTemplate('pivot-seeder')
            ->with([
                'first_resource' => $resources[0],
                'second_resource' => $resources[1],
                'first_table' => $tables[0],
                'second_table' => $tables[1],
                'name' => $name,
                'count' => $this->option('count')
            ])
            ->get();

        $this->save($content, $file, $name);
    }

    protected function getResources()
    {
        $resources = array_map(function($arg) {
            return snake_case(str_singular($this->argument($arg)));
        }, ['model1', 'model2']);

        sort($resources);

        return $resources;
    }

    protected function getSeederName($resources) {
        $resources = array_map(function($resource){
            return ucwords(camel_case($resource));
        }, $resources);
        return implode('', $resources) . 'TableSeeder';
    }

    protected function getTableNames($resources) {
        return array_map('str_plural', $resources);
    }

}
