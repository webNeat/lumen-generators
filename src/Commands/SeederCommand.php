<?php namespace Wn\Generators\Commands;


class SeederCommand extends BaseCommand {

	protected $signature = 'wn:seeder
        {model : full qualified name of the model}
        {--count=10 : number of elements to add in database}
        {--force= : override the existing files}
        {--ns=App\Models : model namespace }
    ';

	protected $description = 'Generates a seeder';

    public function handle()
    {
        $model = $this->option('ns').'\\'.$this->argument('model');
        $name = $this->getSeederName($model);
        $file = "./database/seeds/{$name}.php";

        $content = $this->getTemplate('seeder')
            ->with([
                'model' => $model,
                'name' => $name,
                'count' => $this->option('count')
            ])
            ->get();


        $this->save($content, $file, $name);
    }

    protected function getSeederName($name)
    {
        $name = explode("\\", $name);
        $name = ucwords(str_plural($name[count($name) - 1]));
        $name = $name . 'TableSeeder';
        return $name;
    }

}
