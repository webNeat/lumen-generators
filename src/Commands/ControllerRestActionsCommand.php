<?php namespace Wn\Generators\Commands;


class ControllerRestActionsCommand extends BaseCommand {

	protected $signature = 'wn:controller:rest-actions
		{--force= : override the existing files}';

	protected $description = 'Generates REST actions trait to use into controllers';

    public function handle()
    {
        $content = $this->getTemplate('controller/rest-actions')->get();

        $this->save($content, "./app/Traits/RESTActions.php", "REST actions trait");
    }

}
