<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Test extends Model {

	protected $fillable = [];

	protected $dates = [];

	public function users()
	{
		return $this->hasMany('App\User');
	}

	public function number()
	{
		return $this->hasMany('Phone');
	}
	
}
