<?php namespace Wn\Generators\Argument;


class ArgumentFormatLoader {

	protected $fs;

	protected $loaded;

	public function __construct(Filesystem $fs)
	{
		$this->fs = $fs;
		$this->loaded = [];
	}

	public function load($name)
	{
    	if(! isset($this->loaded[$name])){
    		$path = __DIR__ . "/../../formats/{$name}.json";
    		$json = "";
			try {
				$json = json_decode($this->fs->get($path));
			} catch(\Exception $e) {
				throw new ArgumentFormatException("Unable to read the file '{$path}'");
			}
			if (json_last_error() !== JSON_ERROR_NONE){
				throw new ArgumentFormatException("Error while parsing the JSON file '{$path}'");
			}
			$this->loaded[$name] = $this->buildFormat($json);
		}
		return $this->loaded[$name];
	}

	protected function buildFormat($obj)
	{
		return $this->fillFirstLevel($obj);
	}

	protected function fillFirstLevel($obj)
	{
	    return $this->fill($obj, true);
	}

	protected function fill($obj, $firstLevel = false)
	{
	    if (is_string($obj)) {
	        return $this->fillString($obj, $firstLevel);
	    } else {
	        return $this->fillObject($obj, $firstLevel);
	    }
	}

	protected function fillString($string, $firstLevel = false)
	{
	    list($name, $type, $isArray) = $this->parseName($string);
	    
	    $format = new ArgumentFormat;
	    $format->name = $name;
	    
	    if ($isArray) {
	        $format->type    = 'array';
	        $subFormat       = new Format;
	        $subFormat->type = $type ?: 'string';            
	        $format->format  = $subFormat;
	    } else {
	        $format->type = 'string';
	    }

	    if ($firstLevel) {
	        if ($format->type === 'object') {
	            $format->separator = ':';
	        } elseif ($format->type === 'array') {
	            $format->separator = ',';
	        }
	    }

	    return $format;
	}

	protected function fillObject($obj, $firstLevel=false)
	{
	    $format = new ArgumentFormat;

	    // Resolve type from name
	    if (isset($obj->name)) {
	        list($name, $type) = $this->parseName($obj->name);
	        $format->name = $name;
	        $format->type = $type;
	    }

	    // Fill type if set
	    if (isset($obj->type)) {
	        $format->type = $obj->type;
	    }

	    // Fill default if set
	    if (isset($obj->default)) {
	        $format->default = $obj->default;
	    }
	    
	    // Set separator, default to ':' for objects and ',' for arrays in first level
	    if (in_array($format->type, ['object', 'array'])) {
	        if (isset($obj->separator)) {
	            $format->separator = $obj->separator;
	        } elseif ($firstLevel) {
	           $format->separator = ($format->type === 'object') ? ':':',';
	        }
	    }

	    // Build format recursively
	    if (isset($obj->fields)) {
	        if ($firstLevel) {
	            if (is_array($obj->fields)) {
	                $format->format = array_map([$this, 'fillFirstLevel'], $obj->fields);
	            } else {
	                $format->format = $this->fill($obj->fields, true);
	            }
	        } else {
	            if (is_array($obj->fields)) {
	                $format->format = array_map([$this, 'fill'], $obj->fields);
	            } else {
	                $format->format = $this->fill($obj->fields);
	            }
	        }
	    }

	    return $format;
	}

	protected function parseName($name)
	{
	    $pattern = '/^(?P<attr>\w+)(\[(?P<type>\w+)?\])?/';
	    preg_match($pattern, $name, $matches);

	    $attr = $matches['attr'];
	    $type = empty($matches['type']) ? null : $matches['type'];
	    $isArray = (@$matches[2][0] === '[');
	    
	    return [$attr, $type, $isArray];
	}

}