<?php namespace Tests\Tmp;

use Illuminate\Database\Eloquent\Model;

class TestingModel extends Model {

	protected $fillable = [];

	protected $dates = [];

	public $rules = [
		"name" => "required",
		"age" => "integer|min:13",
		"email" => "email|unique:users,email_address",
	];

	// Relationships

}
