<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model {

	protected $fillable = ["name", "descr"];

	protected $dates = [];

	public static $rules = [
		"name" => "required",
	];

	public function tags()
	{
		return $this->belongsToMany("App\Tag")->withTimestamps();
	}


}
