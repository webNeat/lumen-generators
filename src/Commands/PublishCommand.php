<?php namespace Wn\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class PublishCommand extends Command {

	protected $signature = 'wn:publish
        {--f : force the publishing.}
    ';
	protected $description = 'Publishes the laravel-generators config file to the config folder of your project';
    protected $fs;

    public function __construct(Filesystem $fs)
    {
        parent::__construct();

        $this->fs = $fs;
    }
    
    public function handle()
    {
        $paths = ServiceProvider::pathsToPublish('Wn\Generators\CommandsServiceProvider');
        
        foreach($paths as $from => $to){
            $shortFrom = $this->shortPath($from);
            
            if($this->copyFile($from, $to)) {
                $this->info($shortFrom . ' has succesfully been copied to ' . $this->shortPath($to));
            }else{
                $this->warn($shortFrom . ' could not be copied. To try and force use the --f option.');
            }
        }
    }
    
    private function shortPath($path){
        return str_replace(base_path(), '', realpath($path));
    }
    
    private function copyFile($from, $to){
        if ($this->fs->exists($to) && !$this->option('f')) {
            return false;
        }
        
        $dir = dirname($to);

        if (!$this->fs->isDirectory($dir)) {
            $this->fs->makeDirectory($dir, 0755, true);
        }

        return $this->fs->copy($from, $to);
    }
}
