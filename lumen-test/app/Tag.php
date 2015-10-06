<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model {

	protected $fillable = ["name"];

	protected $dates = [];

	public static $rules = [
		"name" => "required|unique",
	];

	public function projects()
	{
		return $this->belongsToMany("App\Project")->withTimestamps();
	}


}
