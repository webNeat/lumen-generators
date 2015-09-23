<?php namespace Wn\Generators\Argument;

use Wn\Generators\Argument\ArgumentFormat;
use Wn\Generators\Exceptions\ArgumentParserException;


class ArgumentParser {

	protected $format;

	public function __construct(ArgumentFormat $format)
	{
	    $this->format = $format;
	}

	public function parse($args)
	{
		return $this->parseToken($args, $this->format);
	}

	protected function parseToken($token, ArgumentFormat $format){
	    switch($format->type) {
	        case 'string':
	            return $token;
	        case 'number':
	            return $this->parseNumber($token);
	        case 'boolean':
	            return $this->parseBoolean($token, $format->name);
	        case 'array':
	            return $this->parseArray($token, $format->separator, $format->format);
	        case 'object':
	            return $this->parseObject($token, $format->separator, $format->format);
	        default:
	            throw new ArgumentParserException("Unknown format type: '{$format->type}'");
	    }
	}

	protected function parseNumber($token)
	{
	    if(! is_numeric($token)) {
	        throw new ArgumentParserException("Unable to parse '{$token}' as number");
	    }
	    return $token + 0;
	}

	protected function parseBoolean($token, $name)
	{
	    if(in_array($token, ['yes', 'true', '1', $name])) {
	        return true;
	    } else if(in_array($token, ['no', 'false', '0', "!{$name}"])){
	        return false;
	    } else {
	        return null;
	    }
	}

	protected function parseArray($token, $separator, ArgumentFormat $format)
	{
	    $result = [];
	    $tokens = explode($separator, $token);
	    foreach($tokens as $value) {
	        array_push($result, $this->parseToken($value, $format));
	    }
	    return $result;
	}

	protected function parseObject($token, $separator, $fields)
	{
	    $result = [];
	    $tokens = explode($separator, $token);
	    $tokensNumber = count($tokens);

	    $requiredFieldsIndexes = [];
	    $optionalFieldsIndexes = [];
	    foreach($fields as $index => $format) {
	        if($format->default === null) {
	            array_push($requiredFieldsIndexes, $index);
	        } else {
	            array_push($optionalFieldsIndexes, $index);
	        }
	    }
	    $requiredFieldsIndexesNumber = count($requiredFieldsIndexes);

	    if($tokensNumber < $requiredFieldsIndexesNumber) {
	        $requiredFields = array_map(function($index) use ($fields) {
	                return $fields[$index]->name;
	            }, $requiredFieldsIndexes);
	        $requiredFields = implode($separator, $requiredFields);
	        throw new ArgumentParserException("Required field missing: {$tokensNumber} given "
	            . "({$token}) but {$requiredFieldsIndexesNumber} required ({$requiredFields})");
	    }

	    $givenOptionalFieldsIndexes = array_slice(
	        $optionalFieldsIndexes, 0, $tokensNumber - $requiredFieldsIndexesNumber);
	    $notPresentFieldsIndexes = array_slice(
	        $optionalFieldsIndexes, $tokensNumber - $requiredFieldsIndexesNumber);
	    $givenFieldsIndexes = array_merge($requiredFieldsIndexes, $givenOptionalFieldsIndexes);
	    sort($givenFieldsIndexes);

	    // Fill the given fields
	    for($i = 0; $i < $tokensNumber; $i ++) {
	        $fieldFormat = $fields[$givenFieldsIndexes[$i]];
	        $result[$fieldFormat->name] = $this->parseToken($tokens[$i], $fieldFormat);
	    }

	    // Fill other fields with default values
	    foreach($notPresentFieldsIndexes as $index) {
	        $fieldFormat = $fields[$index];
	        $result[$fieldFormat->name] = $fieldFormat->default;
	    }

	    return $result;
	}

}