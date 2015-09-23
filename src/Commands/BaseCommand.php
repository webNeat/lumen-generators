<?php namespace Wn\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Wn\Generators\Argument\ArgumentFormatLoader;
use Wn\Generators\Argument\ArgumentParser;
use Wn\Generators\Template\TemplateLoader;


class BaseCommand extends Command {
    
    protected $fs;
	protected $templates;

	public function __construct(Filesystem $fs)
	{
        parent::__construct();
        
        $this->fs = $fs;
        $this->templates = new TemplateLoader($fs);
        $this->argumentFormatLoader = new ArgumentFormatLoader($fs);
    }

    protected function getTemplate($name)
    {
        return $this->templates->load($name);
    }

    protected function getArgumentParser($name){
        $format = $this->argumentFormatLoader->load($name);
        return new ArgumentParser($format);
    }

    protected function save($content, $path)
    {
        $this->makeDirectory($path);
        $this->fs->put($path, $content);
    }

    protected function makeDirectory($path)
    {
        if (!$this->fs->isDirectory(dirname($path))) {
            $this->fs->makeDirectory(dirname($path), 0777, true, true);
        }
    }

}