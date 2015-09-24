<?php namespace Wn\Generators\Template;

use Wn\Generators\Template\TemplateLoader;


class Template {

	protected $loader;

	protected $text;

	protected $data;

	protected $compiled;

	protected $dirty;

	public function __construct(TemplateLoader $loader, $text)
	{
		$this->loader = $loader;
		$this->text = $text;
		$this->compiled = '';
		$this->data = [];
		$this->dirty = true;
	}

	public function clean()
	{
		$this->data = [];
		$this->dirty = false;
		return $this;
	}

	public function with($data = [])
	{
		if($data)
			$this->dirty = true;
		foreach ($data as $key => $value) {
			$this->data[$key] = $value;
		}
		return $this;
	}

	public function get()
	{
		if($this->dirty){
			$this->compile();
			$this->dirty = false;
		}
		return $this->compiled;
	}

	public function compile()
	{
		$this->compiled = $this->text;
		foreach($this->data as $key => $value){
			$this->compiled = str_replace('{{' . $key . '}}', $value, $this->compiled);
		}
		return $this;
	}	

}