<?php namespace Wn\Generators\Commands;


class FactoryCommand extends BaseCommand {

	protected $signature = 'wn:factory
        {model : full qualified name of the model.}
        {--fields= : the fields to generate.}
        {--file= : the factories file.}
        {--parsed : tells the command that arguments have been already parsed. To use when calling the command from an other command and passing the parsed arguments and options}
        {--force= : override the existing files}
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

        $this->save($content, $file, "{$model} factory", true);
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
            $content = "        // Fields here";
        }

        return $content;
    }

}
