<?php namespace Wn\Generators\Commands;


class SeederCommand extends BaseCommand {

	protected $signature = 'wn:seeder
        {model : full qualified name of the model.}
        {--cout=10 : number of elements to add in database.}
    ';

	protected $description = 'Generates a model factory';

    public function handle()
    {
        $model = $this->argument('model');

        $file = $this->getFile();

        $content = $this->fs->get($file);

        $content .= $this->getTemplate('factory')
            ->with([
                'model' => $model,
                'factory_fields' => $this->getFieldsContent()
            ])
            ->get();

        $this->save($content, $file);

        $this->info("{$model} factory generated !");
    }

    protected function getFile()
    {
        $file = $this->option('file');
        if(! $file){
            $file = './database/factories/ModelFactory.php';
        }
        return $file;
    }

    protected function getFieldsContent()
    {
        $content = [];

        $fields = $this->option('fields');

        if($fields){
            if(! $this->option('parsed')){
                $fields = $this->getArgumentParser('factory-fields')->parse($fields);
            }
            $template = $this->getTemplate('factory/field');
            foreach($fields as $field){
                $content[] = $template->with($field)->get();
            }
            $content = implode(PHP_EOL, $content);
        } else {
            $content = "\t\t// Fields here";
        }

        return $content;
    }
    
}