<?php namespace Wn\Generators\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
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

    protected function save($content, $path, $name, $force = false)
    {
        if (!$force && $this->fs->exists($path) && $this->input->hasOption('force') && !$this->option('force')) {
            $this->info("{$name} already exists; use --force option to override it !");
            return;
        }
        $this->makeDirectory($path);
        $this->fs->put($path, $content);
        $this->info("{$name} generated !");
    }

    protected function makeDirectory($path)
    {
        if (!$this->fs->isDirectory(dirname($path))) {
            $this->fs->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    protected function spaces($n)
    {
        return str_repeat(' ', $n);
    }

}
