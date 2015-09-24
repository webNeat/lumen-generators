<?php namespace Wn\Generators\Commands;


class ControllerRestActionsCommand extends BaseCommand {

	protected $signature = 'wn:controller:rest-actions';

	protected $description = 'Generates REST actions trait to use into controllers';

    public function handle()
    {
        $content = $this->getTemplate('controller/rest-actions')->get();

        $this->save($content, "./app/Http/Controllers/RESTActions.php");

        $this->info("REST actions trait generated !");
    }
    
}