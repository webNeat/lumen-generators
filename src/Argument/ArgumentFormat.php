<?php namespace Wn\Generators\Argument;


class ArgumentFormat {

	/**
	 * Argument name
	 * 
	 * @var string
	 */
	public $name;

	/**
	 * One of the types: array, object, string, boolean, number
	 * 
	 * @var string
	 */
	public $type;
	
	/**
	 * The default value
	 * 
	 * @var string
	 */
	public $default;

	/**
	 * The separator, by default ":" for object and "," for array.
	 * 
	 * @var string
	 */
	public $separator;

	/**
	 * Specify the format of fields for object
	 *     [ field_name => Format, field_name => Format, ... ] 
	 * Specify the format of an element of array
	 * 
	 * @var mixed
	 */
	public $format;
	
}